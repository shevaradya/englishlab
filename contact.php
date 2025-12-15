<?php
require_once 'config/db.php';

$contactSuccess = '';
$contactError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '' || $email === '' || $message === '') {
        $contactError = 'Please fill in all fields before sending.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $contactError = 'Please provide a valid email address.';
    } else {
        // In a real app, this would be emailed or stored.
        $contactSuccess = 'Message sent! Our team will respond within 24 hours.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Interactive English Lab</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'partials/navbar.php'; ?>
    <section class="section-space">
        <div class="container">
            <div class="row g-5 align-items-start">
                <div class="col-lg-5 fade-right">
                    <div class="section-title text-start">
                        <span>Contact</span>
                        <h2>Let’s build fluency together</h2>
                        <p>Have a feature request, need onboarding support, or want to collaborate? Reach out—our team responds quickly.</p>
                    </div>
                    <div class="contact-info">
                        <div class="contact-icon">📧</div>
                        <div>
                            <strong>Email</strong>
                            <p class="mb-0 text-muted">support@ielab.local</p>
                        </div>
                    </div>
                    <div class="contact-info">
                        <div class="contact-icon">📞</div>
                        <div>
                            <strong>Phone</strong>
                            <p class="mb-0 text-muted">+62-831-9476-1904 </p>
                        </div>
                    </div>
                    <div class="contact-info">
                        <div class="contact-icon">📍</div>
                        <div>
                            <strong>HQ</strong>
                            <p class="mb-0 text-muted">Medan, Indonesia</p>
                        </div>
                    </div>
                    <div class="map-wrapper mt-4 fade-up">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1991.0483169286038!2d98.65603037912011!3d3.5652272710182973!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x30312fb39ec31b27%3A0x38672c33c0166edc!2sGedung%20Z%20Politeknik%20Negeri%20Medan!5e0!3m2!1sid!2sid!4v1764771829569!5m2!1sid!2sid" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
                <div class="col-lg-7 fade-left">
                    <div class="contact-form">
                        <?php if ($contactError): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($contactError); ?></div>
                        <?php endif; ?>
                        <?php if ($contactSuccess): ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($contactSuccess); ?></div>
                        <?php endif; ?>
                        <form method="POST" id="contactForm" class="needs-validation" novalidate>
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="name" name="name" placeholder="Jane Doe" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                                <label for="name">Full Name</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="email" class="form-control" id="email" name="email" placeholder="you@email.com" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                                <label for="email">Email</label>
                            </div>
                            <div class="form-floating mb-4">
                                <textarea class="form-control" id="message" name="message" placeholder="How can we help?" required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                                <label for="message">Message</label>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Send message</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <footer>
        <div class="container text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> Interactive English Lab · Support squad on standby.</p>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>

