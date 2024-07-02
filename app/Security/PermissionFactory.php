<?php declare(strict_types = 1);

namespace App\Security;

use App\Models\Address;
use App\Models\CashRegister;
use App\Models\CashRegisterItem;
use App\Models\User;
use Nette\Security\Permission;
use Nette\SmartObject;

class PermissionFactory
{
    use SmartObject;

    /**
     * @return Permission
     */
    public static function create(): Permission
    {
        $permission = new Permission();

        $permission->addResource(User::class);

        $permission->addRole(User::ROLE_GUEST);
        $permission->addRole(User::ROLE_USER, User::ROLE_GUEST);
        $permission->addRole(User::ROLE_ADMIN, User::ROLE_USER);

        $permission->deny([User::ROLE_GUEST, User::ROLE_USER]);

        $permission->allow([User::ROLE_USER, User::ROLE_GUEST], null, null, function (string $resource, string $privilege) {
            if (str_starts_with($resource, 'App\Modules\Admin')) {
                return false;
            }

            return true;
        });

        $permission->allow([User::ROLE_USER, User::ROLE_GUEST], null, null, function (string $resource, string $privilege) {
//            if ($resource === ReportPresenter::class && $privilege === 'actionExport') {
//                return false;
//            }

            return true;
        });

        return $permission;
    }
}