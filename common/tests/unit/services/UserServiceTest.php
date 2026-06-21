<?php

namespace common\tests\unit\services;

use common\models\form\LoginForm;
use common\models\form\RegisterForm;
use common\models\form\VerifyEmailForm;
use common\models\User;
use common\services\UserService;
use common\enums\UserRole;
use Yii;
use yii\web\ConflictHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnauthorizedHttpException;

class UserServiceTest extends \Codeception\Test\Unit
{
    /**
     * @var \common\tests\UnitTester
     */
    protected $tester;

    protected function _after()
    {
        Yii::$app->user->logout();
    }

    public function testLoginSuccess()
    {
        // Set password for a seeded user to be sure
        $user = User::findByUsername('bambang.sudjatmiko');
        $user->setPassword('password123');
        $user->save();

        $model = new LoginForm([
            'username' => 'bambang.sudjatmiko',
            'password' => 'password123',
        ]);

        $result = UserService::login($model);
        $this->assertArrayHasKey('access_token', $result);
        $this->assertNotEmpty($result['access_token']);
        $this->assertFalse(Yii::$app->user->isGuest);
        $this->assertEquals($user->id, Yii::$app->user->id);
    }

    public function testLoginWrongUsername()
    {
        $model = new LoginForm([
            'username' => 'nonexistent_user',
            'password' => 'password123',
        ]);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Username atau password salah');
        UserService::login($model);
    }

    public function testLoginWrongPassword()
    {
        $user = User::findByUsername('bambang.sudjatmiko');
        $user->setPassword('password123');
        $user->save();

        $model = new LoginForm([
            'username' => 'bambang.sudjatmiko',
            'password' => 'wrong_password',
        ]);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Username atau password salah');
        UserService::login($model);
    }

    public function testRegisterSuccess()
    {
        $username = 'newly_registered_user';
        $email = 'newly@example.com';
        $model = new RegisterForm([
            'username' => $username,
            'email' => $email,
            'password' => 'password123',
        ]);

        $result = UserService::register($model);
        $this->assertArrayHasKey('access_token', $result);
        
        $user = User::findByUsername($username);
        $this->assertNotNull($user);
        $this->assertEquals($email, $user->email);
        $this->assertEquals(User::STATUS_INACTIVE, $user->status);
        $this->assertNotNull($user->verification_token);
        
        // Verify role assignment
        $this->assertTrue(Yii::$app->authManager->checkAccess($user->id, UserRole::USER));
        
        // Verify auto login
        $this->assertFalse(Yii::$app->user->isGuest);
        $this->assertEquals($user->id, Yii::$app->user->id);
    }

    public function testRegisterConflictUsername()
    {
        $model = new RegisterForm([
            'username' => 'bambang.sudjatmiko',
            'email' => 'some_new_email@example.com',
            'password' => 'password123',
        ]);

        $this->expectException(ConflictHttpException::class);
        $this->expectExceptionMessage('Username atau email sudah digunakan');
        UserService::register($model);
    }

    public function testRegisterConflictEmail()
    {
        $model = new RegisterForm([
            'username' => 'some_new_user',
            'email' => 'bambang@gmail.com',
            'password' => 'password123',
        ]);

        $this->expectException(ConflictHttpException::class);
        $this->expectExceptionMessage('Username atau email sudah digunakan');
        UserService::register($model);
    }

    public function testVerifyEmailSuccess()
    {
        $user = User::findByUsername('bambang.sudjatmiko');
        $user->status = User::STATUS_INACTIVE;
        $user->generateEmailVerificationToken();
        $user->save();

        $model = new VerifyEmailForm([
            'token' => $user->verification_token,
        ]);

        $result = UserService::verifyEmail($model);
        $this->assertArrayHasKey('access_token', $result);
        
        $user->refresh();
        $this->assertEquals(User::STATUS_ACTIVE, $user->status);
    }

    public function testVerifyEmailWrongToken()
    {
        $model = new VerifyEmailForm([
            'token' => 'invalid_token_12345',
        ]);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Token salah');
        UserService::verifyEmail($model);
    }

    public function testDetailSuccess()
    {
        $user = User::findByUsername('bambang.sudjatmiko');
        $result = UserService::detail($user->id);
        
        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($user->id, $result->id);
        $this->assertEquals($user->username, $result->username);
    }

    public function testDetailNotFound()
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Akun belum terdaftar dalam sistem');
        UserService::detail(999999);
    }

    public function testMeSuccess()
    {
        $user = User::findByUsername('bambang.sudjatmiko');
        Yii::$app->user->login($user);
        
        $result = UserService::me();
        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($user->id, $result->id);
    }

    public function testMeUnauthorized()
    {
        $this->expectException(UnauthorizedHttpException::class);
        $this->expectExceptionMessage('Akun belum terdaftar dalam sistem');
        UserService::me();
    }

    public function testFindSaspriKAsCoordinatorOrFailSuccess()
    {
        $user = User::findOne(14);
        Yii::$app->user->login($user);
        
        $result = UserService::findSaspriKAsCoordinatorOrFail();
        $this->assertNotNull($result);
        // SaspriK ID 1 is assigned to coordinator 14 in the seeder
        $this->assertEquals(1, $result->id);
    }

    public function testFindSaspriKAsCoordinatorOrFailForbidden()
    {
        // User ID 9 is prabowo.subianto, NOT a coordinator of any SaspriK
        $user = User::findOne(9);
        Yii::$app->user->login($user);
        
        $this->expectException(ForbiddenHttpException::class);
        $this->expectExceptionMessage('Hanya wali yang boleh mengakses halaman ini');
        UserService::findSaspriKAsCoordinatorOrFail();
    }
}
