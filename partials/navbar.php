<?php
$activePage = getActivePage();
$loggedIn = isLoggedIn();
$admin = isAdmin();
$inAdmin = str_contains($_SERVER['SCRIPT_NAME'], '/admin/');
$basePath = $inAdmin ? '../' : '';

function navLink(string $label, string $path, array $matches, string $basePath, string $activePage) {
    $isActive = in_array($activePage, $matches, true);
    $href = $basePath . $path;
    return "<li class=\"nav-item\"><a class=\"nav-link " . ($isActive ? 'active' : '') . "\" href=\"{$href}\">{$label}</a></li>";
}
?>

<nav class="navbar navbar-expand-lg ielab-navbar">
    <div class="container">
        <a class="navbar-brand" href="<?php echo $basePath; ?>index.php">Interactive English Lab</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#ielabNav" aria-controls="ielabNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="ielabNav">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <?php
                echo navLink('Home', 'index.php', ['index'], $basePath, $activePage);
                echo navLink('Courses', 'courses.php', ['courses', 'course'], $basePath, $activePage);
                echo navLink('About', 'about.php', ['about'], $basePath, $activePage);
                echo navLink('Contact', 'contact.php', ['contact'], $basePath, $activePage);
                if ($admin) {
                    echo navLink('Admin', 'dashboard_admin.php', ['dashboard_admin'], $basePath, $activePage);
                    echo navLink('Courses', 'admin/list_courses.php', ['list_courses'], $basePath, $activePage);
                    echo navLink('Enrollments', 'admin/list_enrollments.php', ['list_enrollments', 'edit_enrollment'], $basePath, $activePage);
                    echo navLink('Lessons', 'admin/list_lessons.php', ['list_lessons', 'edit_lesson'], $basePath, $activePage);
                    echo navLink('Quizzes', 'admin/list_quizzes.php', ['list_quizzes', 'edit_quiz'], $basePath, $activePage);
                    echo navLink('Students', 'admin/list_students.php', ['list_students'], $basePath, $activePage);
                } elseif ($loggedIn) {
                    echo navLink('My Learning', 'dashboard_student.php', ['dashboard_student'], $basePath, $activePage);
                }
                ?>
                <?php if ($loggedIn): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <?php echo htmlspecialchars($_SESSION['user']['name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?php echo $basePath; ?>profile.php">Profile</a></li>
                            <?php if ($admin): ?>
                                <li><a class="dropdown-item" href="<?php echo $basePath; ?>dashboard_admin.php">Admin Dashboard</a></li>
                            <?php else: ?>
                                <li><a class="dropdown-item" href="<?php echo $basePath; ?>dashboard_student.php">Student Dashboard</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo $basePath; ?>logout.php">Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <button class="btn btn-primary ms-lg-3" data-bs-toggle="modal" data-bs-target="#authModal">
                            Login / Sign Up
                        </button>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<?php if (!$loggedIn): ?>
<div class="modal fade" id="authModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">Access Interactive English Lab</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-pills mb-3" id="authTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="login-tab" data-bs-toggle="pill" data-bs-target="#login-pane" type="button" role="tab">
                            Login
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="signup-tab" data-bs-toggle="pill" data-bs-target="#signup-pane" type="button" role="tab">
                            Sign Up
                        </button>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="login-pane" role="tabpanel">
                        <form action="<?php echo $basePath; ?>login.php" method="POST" class="needs-validation" novalidate>
                            <input type="hidden" name="from_modal" value="1">
                            <div class="form-floating mb-3">
                                <input type="email" class="form-control" id="modalLoginEmail" name="email" placeholder="name@email.com" required>
                                <label for="modalLoginEmail">Email</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="password" class="form-control" id="modalLoginPassword" name="password" placeholder="Password" required>
                                <label for="modalLoginPassword">Password</label>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Login</button>
                        </form>
                    </div>
                    <div class="tab-pane fade" id="signup-pane" role="tabpanel">
                        <form action="<?php echo $basePath; ?>register.php" method="POST" class="needs-validation" novalidate>
                            <input type="hidden" name="from_modal" value="1">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="modalName" name="name" placeholder="Full Name" required>
                                <label for="modalName">Full Name</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="email" class="form-control" id="modalEmail" name="email" placeholder="name@email.com" required>
                                <label for="modalEmail">Email</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="password" class="form-control" id="modalPassword" name="password" placeholder="Password" minlength="6" required>
                                <label for="modalPassword">Password</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="password" class="form-control" id="modalConfirmPassword" name="confirm_password" placeholder="Confirm Password" minlength="6" required>
                                <label for="modalConfirmPassword">Confirm Password</label>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Create Account</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

