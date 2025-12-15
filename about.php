<?php
require_once 'config/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Interactive English Lab</title>
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
            <div class="section-title fade-up">
                <span>Who we are</span>
                <h2>We craft learning experiences that feel like play</h2>
                <p>Interactive English Lab is built by teachers, creatives, and technologists who believe language learning should be joyful, habit-forming, and measurable.</p>
            </div>
            <div class="row g-4 align-items-center">
                <div class="col-lg-6 fade-right">
                    <div class="soft-card">
                        <h4>Our Mission</h4>
                        <p class="text-muted">Help every learner build confidence in English through micro-lessons, conversational practice, and meaningful feedback loops.</p>
                        <ul class="mt-4 list-unstyled">
                            <li class="mb-3">🌱 Lower the barrier to entry with playful UI and daily streaks.</li>
                            <li class="mb-3">🎯 Track actionable metrics like XP, mastery, and quiz accuracy.</li>
                            <li class="mb-3">🤝 Empower educators with admin insights and rapid course creation tools.</li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-6 fade-left">
                    <div class="values-grid">
                        <div class="soft-card">
                            <h5>Human + AI</h5>
                            <p class="text-muted mb-0">We blend human storytelling with smart automations for feedback that feels personal.</p>
                        </div>
                        <div class="soft-card">
                            <h5>Progress you can feel</h5>
                            <p class="text-muted mb-0">XP, levels, and badges turn nebulous progress into daily wins.</p>
                        </div>
                        <div class="soft-card">
                            <h5>Global-first</h5>
                            <p class="text-muted mb-0">Our lessons feature authentic voices, accents, and cultural cues.</p>
                        </div>
                        <div class="soft-card">
                            <h5>Accessibility</h5>
                            <p class="text-muted mb-0">Optimized for mobile, offline moments, and inclusive UX guidelines.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="section-space bg-white">
        <div class="container">
            <div class="section-title fade-up">
                <span>Our Process</span>
                <h2>From spark to spoken fluency</h2>
            </div>
            <div class="timeline-step fade-up">
                <span>01</span>
                <div>
                    <h5>Discover</h5>
                    <p class="text-muted mb-0">We interview learners weekly to understand their blockers and dreams.</p>
                </div>
            </div>
            <div class="timeline-step fade-up" data-delay="150">
                <span>02</span>
                <div>
                    <h5>Design & prototype</h5>
                    <p class="text-muted mb-0">We storyboard micro-lessons and prototype gamified flows in days.</p>
                </div>
            </div>
            <div class="timeline-step fade-up" data-delay="300">
                <span>03</span>
                <div>
                    <h5>Deliver & iterate</h5>
                    <p class="text-muted mb-0">Launch, measure XP gains, run A/B tests, and ship improvements weekly.</p>
                </div>
            </div>
        </div>
    </section>
    <section class="section-space">
    <div class="container">
    <div class="section-title fade-up">
        <span>Meet Our Team</span>
        <h2>System Development</h2>
        <p>This project was developed as part of a final university assignment, focusing on creating an interactive, user-friendly, and effective e-learning platform.</p>
    </div>
</div>

            <div class="row g-4 justify-content-center">
    <div class="col-md-4 fade-up">
        <div class="soft-card team-card text-center">
            <img src="assets/img/sheva.png" 
                 alt="Sheva Radya Raffly" 
                 class="mb-3">

            <h5>Sheva Radya Raffly</h5>
            <p class="text-muted mb-0">2205181055</p>
        </div>
    </div>
</div>

        </div>
    </section>
    <footer>
        <div class="container text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> Interactive English Lab · Built with love and late-night matcha.</p>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>

