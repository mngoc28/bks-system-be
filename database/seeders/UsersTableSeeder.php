<?php
namespace Database\Seeders;

use Faker\Factory as Faker;
use Faker\Generator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class UsersTableSeeder extends Seeder
{
    private Generator $faker;
    private array $vietNamPrefixes;
    private array $usedEmails = [];

    public function __construct()
    {
        $this->faker           = Faker::create('vi_VN');
        $this->vietNamPrefixes = [
            '032', '033', '034', '035', '036', '037', '038', '039',
            '070', '076', '077', '078', '079',
            '081', '082', '083', '084', '085',
            '056', '058', '059',
        ];
    }

    /**
     * Remove Vietnamese accents from string
     */
    private function removeVietnameseAccents(string $str): string
    {
        $map = [
            'à' => 'a', 'á' => 'a', 'ạ' => 'a', 'ả' => 'a', 'ã' => 'a',
            'â' => 'a', 'ầ' => 'a', 'ấ' => 'a', 'ậ' => 'a', 'ẩ' => 'a', 'ẫ' => 'a',
            'ă' => 'a', 'ằ' => 'a', 'ắ' => 'a', 'ặ' => 'a', 'ẳ' => 'a', 'ẵ' => 'a',
            'è' => 'e', 'é' => 'e', 'ẹ' => 'e', 'ẻ' => 'e', 'ẽ' => 'e',
            'ê' => 'e', 'ề' => 'e', 'ế' => 'e', 'ệ' => 'e', 'ể' => 'e', 'ễ' => 'e',
            'ì' => 'i', 'í' => 'i', 'ị' => 'i', 'ỉ' => 'i', 'ĩ' => 'i',
            'ò' => 'o', 'ó' => 'o', 'ọ' => 'o', 'ỏ' => 'o', 'õ' => 'o',
            'ô' => 'o', 'ồ' => 'o', 'ố' => 'o', 'ộ' => 'o', 'ổ' => 'o', 'ỗ' => 'o',
            'ơ' => 'o', 'ờ' => 'o', 'ớ' => 'o', 'ợ' => 'o', 'ở' => 'o', 'ỡ' => 'o',
            'ù' => 'u', 'ú' => 'u', 'ụ' => 'u', 'ủ' => 'u', 'ũ' => 'u',
            'ư' => 'u', 'ừ' => 'u', 'ứ' => 'u', 'ự' => 'u', 'ử' => 'u', 'ữ' => 'u',
            'ỳ' => 'y', 'ý' => 'y', 'ỵ' => 'y', 'ỷ' => 'y', 'ỹ' => 'y',
            'đ' => 'd',
            'À' => 'A', 'Á' => 'A', 'Ạ' => 'A', 'Ả' => 'A', 'Ã' => 'A',
            'Â' => 'A', 'Ầ' => 'A', 'Ấ' => 'A', 'Ậ' => 'A', 'Ẩ' => 'A', 'Ẫ' => 'A',
            'Ă' => 'A', 'Ằ' => 'A', 'Ắ' => 'A', 'Ặ' => 'A', 'Ẳ' => 'A', 'Ẵ' => 'A',
            'È' => 'E', 'É' => 'E', 'Ẹ' => 'E', 'Ẻ' => 'E', 'Ẽ' => 'E',
            'Ê' => 'E', 'Ề' => 'E', 'Ế' => 'E', 'Ệ' => 'E', 'Ể' => 'E', 'Ễ' => 'E',
            'Ì' => 'I', 'Í' => 'I', 'Ị' => 'I', 'Ỉ' => 'I', 'Ĩ' => 'I',
            'Ò' => 'O', 'Ó' => 'O', 'Ọ' => 'O', 'Ỏ' => 'O', 'Õ' => 'O',
            'Ô' => 'O', 'Ồ' => 'O', 'Ố' => 'O', 'Ộ' => 'O', 'Ổ' => 'O', 'Ỗ' => 'O',
            'Ơ' => 'O', 'Ờ' => 'O', 'Ớ' => 'O', 'Ợ' => 'O', 'Ở' => 'O', 'Ỡ' => 'O',
            'Ù' => 'U', 'Ú' => 'U', 'Ụ' => 'U', 'Ủ' => 'U', 'Ũ' => 'U',
            'Ư' => 'U', 'Ừ' => 'U', 'Ứ' => 'U', 'Ự' => 'U', 'Ử' => 'U', 'Ữ' => 'U',
            'Ỳ' => 'Y', 'Ý' => 'Y', 'Ỵ' => 'Y', 'Ỷ' => 'Y', 'Ỹ' => 'Y',
            'Đ' => 'D',
        ];

        return strtr($str, $map);
    }

    /**
     * Generate unique email based on name
     */
    private function generateUniqueEmail(string $name): string
    {
        $nameParts = explode(' ', $name);
        $firstName = strtolower($this->removeVietnameseAccents($nameParts[0] ?? 'user'));
        $lastName  = strtolower($this->removeVietnameseAccents(end($nameParts) ?? 'test'));
        $firstName = preg_replace('/[^a-z0-9]/', '', $firstName);
        $lastName  = preg_replace('/[^a-z0-9]/', '', $lastName);

        $emailVariations = [
            $firstName . '.' . $lastName . '@gmail.com',
            $firstName . $lastName . '@gmail.com',
            $firstName . $lastName . rand(100, 999) . '@gmail.com',
            $firstName . '.' . rand(1000, 9999) . '@gmail.com',
            $firstName . $this->faker->numerify('####') . '@gmail.com',
            $this->faker->userName() . rand(100, 999) . '@gmail.com',
        ];

        do {
            $email = $this->faker->randomElement($emailVariations);
        } while (in_array($email, $this->usedEmails) || DB::table('users')->where('email', $email)->exists());

        $this->usedEmails[] = $email;
        return $email;
    }

    /**
     * Generate Vietnamese phone number
     */
    private function generatePhone(): string
    {
        $prefix = $this->faker->randomElement($this->vietNamPrefixes);
        $suffix = $this->faker->numerify('#######');
        return $prefix . $suffix;
    }

    /**
     * Create user data array
     */
    private function createUserData(
        string $name,
        string $email,
        string $role,
        string | int $status,
        int $isEmailVerified = 1,
        int $createdBy = 1,
        int $updatedBy = 1
    ): array {
        return [
            'name'              => $name,
            'email'             => $email,
            'is_email_verified' => $isEmailVerified,
            'password'          => Hash::make('password'),
            'role'              => $role,
            'phone'             => $this->generatePhone(),
            'status'            => (string) $status,
            'created_by'        => $createdBy,
            'updated_by'        => $updatedBy,
            'created_at'        => now(),
            'updated_at'        => now(),
        ];
    }

    /**
     * Create multiple users
     */
    private function createUsers(int $count, string $role, array $statusOptions, int $isEmailVerified = 1): void
    {
        $users = [];
        for ($i = 0; $i < $count; $i++) {
            $name    = $this->faker->name();
            $email   = $this->generateUniqueEmail($name);
            $status  = $this->faker->randomElement($statusOptions);
            $users[] = $this->createUserData($name, $email, $role, $status, $isEmailVerified);
        }
        DB::table('users')->insert($users);
    }

    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        DB::table('users')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        // Create admin user (ID = 1)
        DB::table('users')->insert([
            $this->createUserData(
                'Quản trị viên',
                'admin@gmail.com',
                'admin',
                '1',
                1,
                1,
                1
            ),
        ]);

        // Create default partner user
        DB::table('users')->insert([
            $this->createUserData(
                'Nhân viên',
                'partner@gmail.com',
                'partner',
                '1',
                1,
                1,
                1
            ),
        ]);

        // Create 20 partner users
        $this->createUsers(20, 'partner', [0, 1], 1);

        // Create 79 regular users
        $this->createUsers(79, 'user', [0, 1, 2], $this->faker->randomElement([0, 1]));
    }
}
