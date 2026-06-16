<?php
class AuthController extends Controller
{
    private UserModel $users;
    private ClientModel $clients;

    public function __construct()
    {
        $this->users   = new UserModel();
        $this->clients = new ClientModel();
    }

    // GET /login
    public function showLogin(array $params): void
    {
        $flash = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_error']);
        include BASE_PATH . '/app/views/auth/login.php';
    }

    // POST /login
    public function login(array $params): void
    {
        $data   = $this->getBody() ?: $_POST;
        $errors = $this->validate($data, ['email', 'password']);

        if ($errors) {
            $this->error('Validation failed', 422, $errors);
        }

        $user = $this->users->findByEmail(strtolower(trim($data['email'])));

        if (!$user || !$user['is_active'] || !$this->users->verifyPassword($data['password'], $user['password_hash'])) {
            $this->error('Invalid email or password', 401);
        }

        $full = $this->users->findWithRole($user['id']);
        $this->users->updateLastLogin($user['id']);

        $_SESSION['user'] = [
            'id'        => $full['id'],
            'full_name' => $full['full_name'],
            'email'     => $full['email'],
            'phone'     => $full['phone'],
            'role'      => $full['role'],
        ];

        $this->success($_SESSION['user'], 'Login successful');
    }

    // GET /register
    public function showRegister(array $params): void
    {
        include BASE_PATH . '/app/views/auth/register.php';
    }

    // POST /register
    public function register(array $params): void
    {
        $data   = $this->getBody() ?: $_POST;
        $errors = $this->validate($data, ['full_name', 'email', 'phone', 'password']);

        if ($errors) {
            $this->error('Validation failed', 422, $errors);
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email address', 422);
        }

        if ($this->users->findByEmail(strtolower(trim($data['email'])))) {
            $this->error('Email already registered', 409);
        }

        if ($this->users->findByPhone($data['phone'])) {
            $this->error('Phone number already registered', 409);
        }

        $data['email'] = strtolower(trim($data['email']));
        $userId = $this->users->createUser($data, 'client');
        $this->clients->createProfile((int) $userId, []);

        $full = $this->users->findWithRole((int) $userId);
        $_SESSION['user'] = [
            'id'        => $full['id'],
            'full_name' => $full['full_name'],
            'email'     => $full['email'],
            'phone'     => $full['phone'],
            'role'      => $full['role'],
        ];

        $this->created(['user_id' => $userId], 'Registration successful');
    }

    // POST /logout
    public function logout(array $params): void
    {
        session_unset();
        session_destroy();
        $this->success(null, 'Logged out');
    }
}
