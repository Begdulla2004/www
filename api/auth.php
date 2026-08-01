<?php
require_once __DIR__ . '/../lib/json_store.php';
require_once __DIR__ . '/../lib/utils.php';
start_session_safe();

$usersFile = __DIR__ . '/../data/users.json';
$users = read_json($usersFile);

$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'POST') {
  $input = json_decode(file_get_contents('php://input'), true) ?: [];
  $action = $input['action'] ?? '';

  if ($action === 'register') {
    $username = trim($input['username'] ?? '');
    $password = (string)($input['password'] ?? '');
    $full_name = trim($input['full_name'] ?? '');
    $email = trim($input['email'] ?? '');

    if ($username === '' || $password === '') {
      json_response(['status'=>'error','message'=>'Username va parol kerak'], 400);
    }
    foreach ($users as $u) {
      if (strcasecmp($u['username'], $username) === 0) {
        json_response(['status'=>'error','message'=>'Bu username band'], 400);
      }
    }
    $roleDefault = 'free';
    $new = [
      'id' => next_id('u'),
      'username' => $username,
      'password_hash' => password_hash($password, PASSWORD_BCRYPT),
      'full_name' => $full_name ?: $username,
      'email' => $email,
      'role' => $roleDefault,
      'created_at' => gmdate('c')
    ];
    $users[] = $new;
    write_json($usersFile, $users);
    $_SESSION['user'] = $new;
    json_response(['status'=>'ok','user'=>$new]);
  }
  elseif ($action === 'login') {
    $username = trim($input['username'] ?? '');
    $password = (string)($input['password'] ?? '');
    foreach ($users as $u) {
      if (strcasecmp($u['username'], $username) === 0) {
        if (password_verify($password, $u['password_hash'])) {
          $_SESSION['user'] = $u;
          json_response(['status'=>'ok','user'=>$u]);
        }
      }
    }
    json_response(['status'=>'error','message'=>'Login yoki parol xato'], 401);
  }
  elseif ($action === 'set_role') {
    $admin = $_SESSION['user'] ?? null;
    $target_username = trim($input['username'] ?? '');
    $role = trim($input['role'] ?? '');
    if ($target_username === '' || ($role !== 'free' && $role !== 'premium')) {
      json_response(['status'=>'error','message'=>'username va role (free|premium) kerak'], 400);
    }
    foreach ($users as &$u) {
      if (strcasecmp($u['username'], $target_username) === 0) {
        $u['role'] = $role;
        write_json($usersFile, $users);
        // Agar hozir kirgan foydalanuvchi yangilangani bo'lsa, sessiyani ham update qilamiz
        if ($admin && strcasecmp($admin['username'], $target_username) === 0) {
          $_SESSION['user'] = $u;
        }
        json_response(['status'=>'ok','user'=>$u]);
      }
    }
    json_response(['status'=>'error','message'=>'Foydalanuvchi topilmadi'], 404);
  }
  elseif ($action === 'logout') {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
      $params = session_get_cookie_params();
      setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
    json_response(['status'=>'ok']);
  }
  else {
    json_response(['status'=>'error','message'=>'Noto\'g\'ri action'], 400);
  }
} else {
  json_response(['status'=>'error','message'=>'Faqat POST'], 405);
}
