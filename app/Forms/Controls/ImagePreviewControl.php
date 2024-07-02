<?php declare(strict_types=1);

namespace App\Forms\Controls;

use App\Media\DataMedium;
use App\Models\Image;
use App\Services\Deadpool\Deadpool;
use App\Services\DI;
use App\Services\Doctrine\EntityManager;
use Nette;
use Nette\Forms;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;
use Nette\Http\FileUpload;
use Nette\Utils\Html;
use RuntimeException;

class ImagePreviewControl extends BaseControl
{
    public const VALID = ':uploadControlValid';
    private const IMAGE_PREVIEW_WIDTH = 1200;
    private const IMAGE_PREVIEW_HEIGHT = 800;

    /**
     * @var DI
     */
    private DI $di;

    /**
     * @param DI $di
     * @param null $label
     */
    public function __construct(DI $di, $label = null)
    {
        parent::__construct($label);

        $this->di = $di;
        $this->setOption('type', 'file');

        $this->control->type = 'file';
        $this->control->accept = implode(', ', FileUpload::ImageMimeTypes);

        $this->addRule([$this, 'isOk'], Forms\Validator::$messages[self::VALID]);

        $this->monitor(Form::class, static function (Form $form): void {
            if (! $form->isMethod('post')) {
                throw new Nette\InvalidStateException('File upload requires method POST.');
            }
            $form->getElementPrototype()->enctype = 'multipart/form-data';
        });
    }

    /**
     * @param string $type
     * @return string
     */
    public function getHtmlName(string $type = 'file'): string
    {
        return parent::getHtmlName() . '[' . $type . ']';
    }

    /**
     * @param string $additional
     * @return string
     */
    public function getHtmlId(string $additional = ''): string
    {
        return parent::getHtmlId() . ($additional ? '-' . $additional : '');
    }

    /**
     * @return void
     */
    public function loadHttpData(): void
    {
        $entityManager = $this->di->get(EntityManager::class);

        $delete = (bool) $this->form->getHttpData(Form::DATA_TEXT, $this->getHtmlName('delete'));
        if ($delete && $this->value) {
            $entityManager->remove($this->value);
            $this->value = null;
        }

        /** @var FileUpload|null $file */
        $file = $this->form->getHttpData(Form::DATA_FILE, $this->getHtmlName());

        if ($file && $file->isImage() && $file->isOk() && file_exists($file->getTemporaryFile())) {
            $image = ($this->value ?? new Image());
            $image->getStorage()->store(DataMedium::fromFile($file));

            $this->value = $image;
            $entityManager->persist($image);
        }
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function setValue($value): self
    {
        if ($value !== null && ! $value instanceof Image) {
            throw new RuntimeException('Parameter must be null or type of ' . Image::class);
        }

        $this->value = $value;

        return $this;
    }

    /**
     * @return bool
     */
    public function isFilled(): bool
    {
        return $this->value instanceof Image;
    }

    /**
     * @return bool
     */
    public function isOk(): bool
    {
        return $this->value instanceof Image;
    }

    /**
     * @return Html
     */
    public function getControl(): Html
    {
        $this->setOption('rendered', true);
        $control = Html::el('div');

        if ($this->value) {
            $previewUrl = $this->di->get(Deadpool::class)
                ->image($this->value)
                ->width(self::IMAGE_PREVIEW_WIDTH)
                ->height(self::IMAGE_PREVIEW_HEIGHT)
                ->shrinkOnly()
                ->toString();

            $controlOptions = Html::el('div');
            $controlOptions->addHtml(
                Html::el('img')
                    ->setAttribute('src', $previewUrl)
                    ->setAttribute('style', 'margin-bottom: 5px; display: block; max-width: 100%;')
            );
            $controlOptions->addHtml(
                Html::el('input')->addAttributes([
                    'type' => 'checkbox',
                    'name' => $this->getHtmlName('delete'),
                    'id' => $this->getHtmlId('delete'),
                    'disabled' => $this->isDisabled(),
                    'style' => 'margin-right: 5px;',
                ])
            );
            $controlOptions->addHtml(
                Html::el('label')->addAttributes([
                    'for' => $this->getHtmlId('delete'),
                    'style' => 'cursor: pointer;'
                ])->setText('🗑')
            );
            $control->addHtml($controlOptions);
        }

        $control->addHtml(
            (clone $this->control)->addAttributes([
                'name' => $this->getHtmlName(),
                'id' => $this->getHtmlId(),
                'required' => $this->isRequired(),
                'disabled' => $this->isDisabled(),
                'data-nette-rules' => Nette\Forms\Helpers::exportRules($this->getRules()) ?: null,
            ])
        );

        return $control;
    }
}
