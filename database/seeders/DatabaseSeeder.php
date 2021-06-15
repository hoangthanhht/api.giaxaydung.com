<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;
use App\Models\Permission;
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        $admin = new Role();
        $admin->slug = 'Admin';
        $admin->name = 'Admin';
        $admin->save();


        $manage = new Role();
        $manage->slug = 'Manage';
        $manage->name = 'Manage';
        $manage->save();


        $manager_role = new Role();
        $manager_role->slug = 'SuperAdmin';
        $manager_role->name = 'Sys Manager';
        $manager_role->save();

        $Users = new Role();
        $Users->slug = 'User';
        $Users->name = 'User';
        $Users->save();

        $guest = new Role();
        $guest->slug = 'Guest';
        $guest->name = 'Guest';
        $guest->save();

        $createTasks = new Permission();
        $createTasks->slug = 'create-tasks';
        $createTasks->name = 'Create Tasks';
        $createTasks->save();

        $deleteTasks = new Permission();
        $deleteTasks->slug = 'delete-tasks';
        $deleteTasks->name = 'Delete Tasks';
        $deleteTasks->save();

        $editTasks = new Permission();
        $editTasks->slug = 'edit-tasks';
        $editTasks->name = 'Edit Tasks';
        $editTasks->save();

        $viewUsers = new Permission();
        $viewUsers->slug = 'view-users';
        $viewUsers->name = 'View Users';
        $viewUsers->save();

        $editUsers = new Permission();
        $editUsers->slug = 'edit-users';
        $editUsers->name = 'Edit Users';
        $editUsers->save();

        $deleteUsers = new Permission();
        $deleteUsers->slug = 'delete-users';
        $deleteUsers->name = 'Delete Users';
        $deleteUsers->save();

        $createUsers = new Permission();
        $createUsers->slug = 'create-users';
        $createUsers->name = 'Create Users';
        $createUsers->save();

        $admin_role = Role::where('slug','Admin')->first();
        $SuperAdmin_role = Role::where('slug', 'SuperAdmin')->first();
        $manager_role = Role::where('slug', 'Manager')->first();
        $user_role = Role::where('slug', 'User')->first();
        $Guest_role = Role::where('slug', 'Guest')->first();

        $create_perm = Permission::where('slug','create-tasks')->first();
        $delete_perm = Permission::where('slug','delete-tasks')->first();
        $edit_perm = Permission::where('slug','edit-tasks')->first();
        $view_perm = Permission::where('slug','view-users')->first();
        $edit_perm = Permission::where('slug','edit-users')->first();
        $delete_perm = Permission::where('slug','delete-users')->first();
        $create_perm = Permission::where('slug','create-users')->first();

        $create_1 = new User();
        $create_1->name = 'user1';
        $create_1->email = 'user1@gmail.com';
        $create_1->password = bcrypt('123123');
        $create_1->save();
        $create_1->roles()->attach($admin_role);
        $create_1->permissions()->attach($create_perm);


        $create_2 = new User();
        $create_2->name = 'user2';
        $create_2->email = 'user2@gmail.com';
        $create_2->password = bcrypt('123123');
        $create_2->save();
        $create_2->roles()->attach($SuperAdmin_role);
        $create_2->permissions()->attach($create_perm);
        $create_2->permissions()->attach($delete_perm);

        $create_3 = new User();
        $create_3->name = 'user3';
        $create_3->email = 'user3@gmail.com';
        $create_3->password = bcrypt('123123');
        $create_3->save();
        $create_3->roles()->attach($manager_role);
        $create_3->permissions()->attach($createTasks);
        $create_3->permissions()->attach($edit_perm);
        $create_3->permissions()->attach($delete_perm);

        $create_4 = new User();
        $create_4->name = 'user4';
        $create_4->email = 'user4@gmail.com';
        $create_4->password = bcrypt('123123');
        $create_4->save();
        $create_4->roles()->attach($user_role);
        $create_4->permissions()->attach($createTasks);

        $create_5 = new User();
        $create_5->name = 'user5';
        $create_5->email = 'user5@gmail.com';
        $create_5->password = bcrypt('123123');
        $create_5->save();
        $create_5->roles()->attach($Guest_role);
        
    }
}
