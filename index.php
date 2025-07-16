<?php
define('NO_HEADER_HTML', true);
require_once __DIR__ . '/includes/header.php';
$login_error = false;
$register_error = false;
$register_success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login_submit'])) {
        $username = trim($_POST['login_username'] ?? '');
        $password = $_POST['login_password'] ?? '';
        if ($username && $password) {
            if (login_user($username, $password)) {
                if (is_admin())
                    header('Location: admin/dashboard.php');
                elseif (is_manager())
                    header('Location: manager/dashboard.php');
                else
                    header('Location: member/dashboard.php');
                exit;
            } else {
                $login_error = 'Invalid username or password.';
            }
        } else {
            $login_error = 'Please fill in all fields.';
        }
    } elseif (isset($_POST['register_submit'])) {
        $username = trim($_POST['register_username'] ?? '');
        $email = trim($_POST['register_email'] ?? '');
        $password = $_POST['register_password'] ?? '';
        $role_id = 3; // Default to member
        if ($username && $email && $password) {
            if (register_user($username, $email, $password, $role_id)) {
                $register_success = 'Registration successful! You can now log in.';
            } else {
                $register_error = 'Registration failed. Username or email may already exist.';
            }
        } else {
            $register_error = 'Please fill in all fields.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>The Best Project Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <style>
        html,
        body {
            margin: 0;
            padding: 0;
            height: 100%;
            min-height: 100vh;
            overflow: hidden;
        }

        body {
            height: 100vh;
            min-height: 100vh;
            width: 100vw;
            font-family: 'Inter', sans-serif;
            background: linear-gradient(90deg, #a770ef 0%, #cf8bf3 50%, #fdb99b 100%);
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
        }

        .top-bar {
            width: 80%;
            position: fixed;
            top: 10px;
            left: 50%;
            transform: translateX(-50%);
            /* This centers it */
            background: #000;
            border-radius: 10px;
            padding: 0.5rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px;
            z-index: 1000;
            /* Ensure it stays above content */
        }


        .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .logo-img {
            /* width: auto;
            height: 45px;
            border-radius: 6px;
            background: whitesmoke; */
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo-img img {
            width: auto;
            height: 70px;
            border-radius: 6px;
            background: whitesmoke;
        }

        .logo-text {
            color: #ffb300;
            font-weight: 700;
            font-size: 1.15rem;
            line-height: 1.1;
        }

        .logo-text span {
            font-weight: 400;
            font-size: 0.95rem;
        }

        .auth-btns .btn {
            font-size: 1rem;
            border-radius: 0.7rem;
            padding: 0.35rem 1rem;
        }

        .auth-btns .btn-signup {
            margin-left: 0.5rem;
            background: #fff;
            color: #000;
            border: none;
        }

        .auth-btns .btn-signup i {
            color: #4f46e5;
        }

        .main-hero {
            min-height: 100vh;
            height: 100vh;
            width: 100vw;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 0 1rem;
        }

        .main-title {
            font-size: 3.5rem;
            font-weight: 800;
            color: #fff;
            margin-top: 2.5rem;
            margin-bottom: 1.2rem;
        }

        .main-desc {
            font-size: 1.3rem;
            color: #f3f3f3;
            margin-bottom: 2rem;
        }

        .get-started-btn {
            font-size: 1.15rem;
            font-weight: 600;
            padding: 0.7rem 2.2rem;
            border-radius: 1rem;
            background: #fff;
            color: #000;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 0.7rem;
            box-shadow: 0 4px 16px rgba(80, 80, 180, 0.2);
            margin-bottom: 2.5rem;
            transition: all 0.3s ease-in-out;
        }

        .get-started-btn i {
            color: #4f46e5;
            font-size: 1.3rem;
        }

        .code-visual {
            position: relative;
            width: 70%;
        }

        .code-block {
            background: #181c23;
            border-radius: 1.2rem;
            padding: 1rem;
            color: #fff;
            font-family: 'Fira Mono', monospace;
            font-size: 1.1rem;
            text-align: left;
            box-shadow: 0 2px 16px rgba(0, 0, 0, 0.13);
            position: relative;
        }

        .code-block .reason {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .code-block .code-block .keyword {
            color: #4f46e5;
        }

        .code-block .fn {
            color: #ffb300;
        }

        .code-block .comment {
            color: #6ee7b7;
        }

        .code-block .type {
            color: #f472b6;
        }



        @media (max-width: 768px) {
            .main-title {
                font-size: 2rem;
            }

            .main-desc {
                font-size: 1rem;
            }

            .code-image {
                right: 0;
                bottom: -30px;
                width: 100px;
            }
        }
    </style>
</head>

<body>

    <div class="top-bar">
        <div class="logo">
            <div class="logo-img">
                <img src="assets/img/logo.png" alt="Best Logo">
            </div>

        </div>
        <div class="auth-btns">
            <a href="#" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#loginModal">Sign in</a>
            <a href="#" class="btn btn-signup" data-bs-toggle="modal" data-bs-target="#registerModal"><i
                    class="bi bi-plus"></i> Sign up</a>
        </div>
    </div>

    <div class="main-hero">
        <div class="main-title">The Best Project Management System</div>
        <div class="main-desc">
            Built to make you extra-ordinarily productive. This tool is the best way to manage your projects with ease
            and speed.
        </div>
        <a href="#" class="get-started-btn" data-bs-toggle="modal" data-bs-target="#registerModal"><i
                class="bi bi-plus"></i> Get Started</a>

        <div class="code-visual">
            <div class="code-block">
                <div class="reason">
                    <h2>MIDSEM PROJECT GROUP 13 {L 200}</h2>
                </div>
                <pre>
<span class="keyword">impl</span> <span class="fn">Project_Management_Team</span> {
  <span class="comment">// Project Contributors</span>
  <span class="keyword">let</span> <span class="var">DatabaseManager</span> = <span class="type">"Benedict Edem Agbezuge"</span>; <span class="comment">// 1705566820</span>
  <span class="keyword">let</span> <span class="var">DocumentationLead</span> = <span class="type">"Odette Chialoka Precious Oforji"</span>;   <span class="comment">// 1692901307</span>
  <span class="keyword">let</span> <span class="var">FrontendEngineer</span> = <span class="type">"John Aworo Junior"</span>;    <span class="comment">// 1688490928</span>
  <span class="keyword">let</span> <span class="var">Coodinator</span> = <span class="type">"Michelle Nikabou"</span>;         <span class="comment">// 1698167319</span>
  <span class="keyword">let</span> <span class="var">UIAssistant</span> = <span class="type">"Koranteng Daniella"</span>;        <span class="comment">// 1703330617</span>
  <span class="comment">// Together, we ship excellence.</span>
}
</pre>
                <!-- <img src="public/anime-girl.png" class="code-image" alt="Finish That Project Now!" /> -->
            </div>
        </div>


    </div>

    <!-- Add modals for login and registration -->
    <div class="modal fade" id="loginModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post">
                    <div class="modal-header">
                        <h5 class="modal-title">Sign In</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <?php if ($login_error): ?>
                            <div class="alert alert-danger p-2"><?php echo $login_error; ?></div><?php endif; ?>
                        <div class="mb-2">
                            <label class="form-label">Username or Email</label>
                            <input type="text" name="login_username" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Password</label>
                            <input type="password" name="login_password" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="login_submit" class="btn btn-primary">Sign In</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="registerModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post">
                    <div class="modal-header">
                        <h5 class="modal-title">Sign Up</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <?php if ($register_error): ?>
                            <div class="alert alert-danger p-2"><?php echo $register_error; ?></div><?php endif; ?>
                        <?php if ($register_success): ?>
                            <div class="alert alert-success p-2"><?php echo $register_success; ?></div><?php endif; ?>
                        <div class="mb-2">
                            <label class="form-label">Username</label>
                            <input type="text" name="register_username" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Email</label>
                            <input type="email" name="register_email" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Password</label>
                            <input type="password" name="register_password" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="register_submit" class="btn btn-primary">Sign Up</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>