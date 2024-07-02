<?php

namespace App\Modules\Admin\Presenters;

use App\Forms\Form;
use App\Helpers\UrlHelper;
use App\Media\DataMedium;
use App\Models\Image;
use App\Models\User;
use App\Services\User\Password;
use Nette\Application\AbortException;
use Nette\Forms\Form as LegacyForm;
use Nette\Forms\IControl;
use Nette\Utils\ImageException;
use Throwable;

final class AccountPresenter extends BasePresenter
{
    /**
     * @var Password
     * @inject
     */
    public Password $password;

    /**
     * @return Form
     */
    public function createComponentEditForm(): Form
    {
        $form = new Form();

        $form->addUpload('image', $this->translator->translate('forms.newImage'))
            ->addRule(LegacyForm::IMAGE, 'Obrázek musí být JPEG, PNG nebo GIF')
            ->addRule(LegacyForm::MAX_FILE_SIZE, 'Maximální velikost souboru jsou 2 MB', 2 * 1024 * 1024);

        // -------------

        $form->addGroup($this->translator->translate('forms.generalParameters'));

        $form->addText('firstName', $this->translator->translate('forms.firstName') . ' *')
            ->setOption('id', 'firstName')
            ->setRequired($this->translator->translate('validations.RequiredField'));

        $form->addText('lastName', $this->translator->translate('forms.lastName') . ' *')
            ->setOption('id', 'lastName')
            ->setRequired($this->translator->translate('validations.RequiredField'));

        $form->addRadioList('sex', 'Pohlaví *', User::TYPE_SEX)
            ->setHtmlAttribute('class', 'form-check-input')
            ->setRequired('Pohlaví je povinné');

        $form->addText('phone', $this->translator->translate('forms.phone') . ' *')
            ->setRequired($this->translator->translate('validations.RequiredField'));

        $form->addText('email', $this->translator->translate('forms.email') . ' *')
            ->setDisabled()
            ->setOmitted();

        $form->addHidden('id');

        $form->addSubmit('update', $this->translator->translate('actions.Save'));

        $form->setDefaults([
            'id' => $this->userEntity->getId(),
            'firstName' => $this->userEntity->getFirstname(),
            'lastName' => $this->userEntity->getLastname(),
            'sex' => $this->userEntity->getSex(),
            'phone' => $this->userEntity->getPhone(),
            'email' => $this->userEntity->getEmail()
        ]);

        $form->onSuccess[] = [$this, 'processForm'];

        return $form;
    }

    /**
     * @param Form $form
     * @return never
     * @throws AbortException
     */
    public function processForm(Form $form): never
    {
        $values = $form->getValues();

        if (! $user = $this->userRepository->find($values->id)) {
            $this->flashMessage($this->translator->translate('errors.accountAlreadyExist'), self::FM_ERROR);
            $this->redirect('this');
        }

        $user
            ->setPhone($values->phone)
            ->setFirstname($values->firstName)
            ->setLastname($values->lastName)
            ->setSex($values->sex);

        $this->entityManager->flush();
        $this->flashTranslatedMessage('messages.RowWasEdited');

        if ($url = UrlHelper::restore('AccountPostUpdate')) {
            $this->redirectUrl($url);
        }

        $this->redirect('default');
    }

    /**
     * @return Form
     */
    public function createComponentUpdatePasswordForm(): Form
    {
        $form = new Form();

        $form->addPassword('password', $this->translator->translate('forms.password') . ' *')
            ->setRequired($this->translator->translate('validations.password'))
            ->setHtmlAttribute('placeholder', $this->translator->translate('forms.placeholder.password'))
            ->addRule(function (IControl $control) {
                $password = $control->getValue();
                $containsUppercase = preg_match('/[A-Z]/', $password);
                $containsLowercase = preg_match('/[a-z]/', $password);
                $containsNumber = preg_match('/\d/', $password);
                $isLongEnough = strlen($password) >= User::PASSWORD_MIN_LENGTH;

                return $containsUppercase && $containsLowercase && $containsNumber && $isLongEnough;
            }, $this->translator->translate('errors.registerForm.password'));

        $form->addPassword('passwordRepeat', $this->translator->translate('forms.newPasswordConfirmation') . ' *')
            ->setRequired($this->translator->translate('validations.RequiredField'))
            ->addRule($form::EQUAL,$this->translator->translate('validations.EnteredPasswordsDoNotMatch'), $form['password']);

        $form->addSubmit('submit', $this->translator->translate('actions.Save'));

        $form->onSuccess[] = function (Form $form) {
            $values = $form->getValues(true);
            $this->userEntity
                ->setPassword($this->password->hash($values['password']))
                ->cleanResetToken();

            $this->entityManager->flush();
            $this->flashTranslatedMessage('messages.Saved');
            $this->redirect('default');
        };

        return $form;
    }

    /**
     * @return Form
     */
    public function createComponentUpdateImageForm(): Form
    {
        $form = new Form();

        $form->addUpload('image', $this->translator->translate('forms.newImage'))
            ->addRule(LegacyForm::IMAGE, 'Obrázek musí být JPEG, PNG nebo GIF')
            ->addRule(LegacyForm::MAX_FILE_SIZE, 'Maximální velikost souboru jsou 2 MB', 2 * 1024 * 1024);


        $form->addHidden('croppedImage')
            ->setHtmlId('croppedImage');

        $form->addSubmit('submit', $this->translator->translate('actions.Save'));

        if ($this->userEntity->hasImage()) {
            $form->addSubmit('delete', $this->translator->translate('actions.Delete'));
        }

        $form->onSuccess[] = [$this, 'updateImageFormSuccess'];

        return $form;
    }

    /**
     * @param Form $form
     * @param array $values
     * @return never
     * @throws AbortException
     * @throws ImageException
     */
    public function updateImageFormSuccess(Form $form, array $values): never
    {
        if ($imageEntity = $this->userEntity->getImage()) {
            $this->userEntity->setImage(null);
            $this->entityManager->remove($imageEntity);
        }

        $fileName = base_convert((string) hrtime(true), 10, 36) . '-' . mt_rand() . '.webp';
        $base = trim(preg_replace('~^(.*?;base64,)~', '', $values['croppedImage']));
        try {
            $data = base64_decode($base);
            $handle = fopen('php://temp', 'rwb+');
            fwrite($handle, $data);
            rewind($handle);
            $exif = @ exif_read_data($handle);
            fclose($handle);
        } catch (Throwable) {

        }

        $image = \Nette\Utils\Image::fromString($data);

        if (! empty($exif) && isset($exif['Orientation'])) {
            // https://www.daveperrett.com/articles/2012/07/28/exif-orientation-handling-is-a-ghetto/#eh-exif-orientation
            switch ($exif['Orientation']) {
                case 1:
                    break;

                case 2:
                    $image->flip(IMG_FLIP_HORIZONTAL);
                    break;

                case 3:
                    $image->rotate(180.0, 0);
                    break;

                case 4:
                    $image->rotate(180.0, 0);
                    $image->flip(IMG_FLIP_HORIZONTAL);
                    break;

                case 5:
                    $image->rotate(-90.0, 0);
                    $image->flip(IMG_FLIP_HORIZONTAL);
                    break;

                case 6:
                    $image->rotate(-90.0, 0);
                    break;

                case 7:
                    $image->rotate(90.0, 0);
                    $image->flip(IMG_FLIP_HORIZONTAL);
                    break;

                case 8:
                    $image->rotate(90.0, 0);
                    break;
            }
        }

        $filePath = DIR_WWW . DIRECTORY_SEPARATOR . 'userImages' . DIRECTORY_SEPARATOR . $fileName;

        $image->save($filePath);

        $image = new Image();
        $image->getStorage()->store(DataMedium::fromPath($filePath));
        $this->userEntity->setImage($image);
        $this->entityManager->persist($image);

        $this->entityManager->flush();

        $this->flashTranslatedMessage('messages.Saved');
        $this->redirect('default');
    }
}
