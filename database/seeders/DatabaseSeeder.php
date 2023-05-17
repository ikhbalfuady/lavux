<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Users;
use App\Models\Permissions;
use App\Models\RolePermissions;
use App\Models\RoleGroups;
use App\Models\Roles;
use App\Models\Notifications;
use App\Models\UserNotifications;
use App\Models\Metas;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->DefaultData();
        $this->DummyData();
    }

    public function DefaultData () {
        $moduleDefault = config('lv_modules', []);

        $logging = [
            'created_by' => 1,
            'created_at' => H_today(),
        ];

        $defaultPermissions = [
            'browse',
            'create',
            'read',
            'update',
            'delete',
            'restore',
        ];

        $roleGroup = RoleGroups::create([
            'name' => 'Super Admin',
            ...$logging,
        ]);

        $role = Roles::create([
            'role_group_id' => $roleGroup->id,
            'name' => 'Super Admin',
            'slug' => 'SA',
            ...$logging,
        ]);

        // init all module permissions
        foreach ($moduleDefault as $mdl) {
            $module = H_splitUppercaseWithSpace($mdl);

            foreach ($defaultPermissions as $prm) {
                $permission = Permissions::create([
                    'module' => $module,
                    'name' => $prm,
                    ...$logging,
                ]);

                RolePermissions::create([
                    'permission_id' => $permission->id,
                    'role_id' => $role->id,
                    ...$logging,
                ]);

            }

        }

        // init custom permissions 
        $customPermissions = [
            [
                'module' => 'Global', 
                'permission' => 'settings', 
            ],
        ];

        foreach ($customPermissions as $mdl) {
            $module = H_splitUppercaseWithSpace($mdl['module']);
            $prm = $mdl['permission'];

            $permission = Permissions::create([
                'module' => $module,
                'name' => $prm,
                ...$logging,
            ]);

            RolePermissions::create([
                'permission_id' => $permission->id,
                'role_id' => $role->id,
                ...$logging,
            ]);
        }

        $user = Users::create([
            'name'  => 'boss',
            'username'  => 'boss',
            'email' => 'boss@mail.com',
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'role_id' => 1,
            'picture' => 'https://avatars.githubusercontent.com/u/8433068?v=4',
            ...$logging,
        ]);

        $user = Users::create([
            'name'  => 'admin',
            'username'  => 'admin',
            'email' => 'admin@mail.com',
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'role_id' => 1,
            'picture' => 'https://media.licdn.com/dms/image/C5616AQF7fBoQIuvumw/profile-displaybackgroundimage-shrink_200_800/0/1596893586598?e=2147483647&v=beta&t=p-rrXK762DR32CGsBGVUwDNiDWaAiW3IaGOpxsuLWNI',
            ...$logging,
        ]);


    }

    public function DummyData () {
        $logging = [
            'created_by' => 1,
            'created_at' => H_today(),
        ];

        $notif = Notifications::create([
            'title' => 'Welcome to Lavux',
            'content' => "Hello, hope you enjoy using lavux, let's see what lavux have",
            'type' => 'system',
            'category' => null,
            'link_source' => 'frontend',
            'link_url' => '/example',
            'date' => H_today(),
            ...$logging,
        ]);

        UserNotifications::create([
            'user_id' => 1,
            'notification_id' => $notif->id,
            ...$logging,
        ]);

        UserNotifications::create([
            'user_id' => 2,
            'notification_id' => $notif->id,
            ...$logging,
        ]);

        //

        $notif = Notifications::create([
            'title' => 'Notif Category',
            'content' => "kategory khusus",
            'type' => 'system',
            'category' => 'PurchaseOrders',
            'link_source' => 'frontend',
            'link_url' => '/example',
            'date' => H_today(),
            ...$logging,
        ]);

        UserNotifications::create([
            'user_id' => 1,
            'is_read' => 1,
            'notification_id' => $notif->id,
            ...$logging,
        ]);
 
        $notif = Notifications::create([
            'title' => 'Permintaan Tambahan',
            'content' => "halo ini adalah direct message dari admin, coba lihat LDBX deh",
            'type' => 'direct',
            'category' => null,
            'link_source' => 'external',
            'link_url' => 'https://github.com/ikhbalfuady/ldbx',
            'date' => H_today(),
            'created_by' => 2,
            'created_at' => H_today(),
        ]);

        UserNotifications::create([
            'user_id' => 1,
            'notification_id' => $notif->id,
            ...$logging,
        ]);

        $notif = Notifications::create([
            'title' => 'Reply: Permintaan Tambahan',
            'content' => "siap min, sudah di proses",
            'type' => 'direct',
            'category' => null,
            'link_source' => 'external',
            'link_url' => 'https://github.com/ikhbalfuady/ldbx',
            'date' => H_today(),
            'created_by' => 1,
            'created_at' => H_today(),
        ]);

        UserNotifications::create([
            'user_id' => 2,
            'notification_id' => $notif->id,
            ...$logging,
        ]);

        $notif = Notifications::create([
            'title' => 'Notif lain',
            'content' => "halo ini adalah direct message dari admin, coba lihat LDBX deh",
            'type' => 'direct',
            'category' => null,
            'link_source' => 'external',
            'link_url' => 'https://github.com/ikhbalfuady/ldbx',
            'date' => H_today(),
            'created_by' => 2,
            'created_at' => H_today(),
        ]);

        UserNotifications::create([
            'user_id' => 1,
            'notification_id' => $notif->id,
            ...$logging,
        ]);

        $notif = Notifications::create([
            'title' => 'Notif lain nya lagi',
            'content' => "halo ini adalah direct message dari admin, coba lihat LDBX deh",
            'type' => 'direct',
            'category' => null,
            'link_source' => 'external',
            'link_url' => 'https://github.com/ikhbalfuady/ldbx',
            'date' => H_today(),
            'created_by' => 2,
            'created_at' => H_today(),
        ]);

        UserNotifications::create([
            'user_id' => 1,
            'notification_id' => $notif->id,
            ...$logging,
        ]);

        $notif = Notifications::create([
            'title' => 'hati hati',
            'content' => "halo ini adalah direct message dari admin, coba lihat LDBX deh",
            'type' => 'direct',
            'category' => null,
            'link_source' => 'external',
            'link_url' => 'https://github.com/ikhbalfuady/ldbx',
            'date' => H_today(),
            'created_by' => 2,
            'created_at' => H_today(),
        ]);

        UserNotifications::create([
            'user_id' => 1,
            'notification_id' => $notif->id,
            ...$logging,
        ]);

        // your dummy data here
    }

}
