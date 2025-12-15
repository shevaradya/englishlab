// Interactive English Lab - Modern Interactions

document.addEventListener('DOMContentLoaded', () => {
    initTooltips();
    initSmoothScroll();
    initAutoDismissAlerts();
    initButtons();
    initObserverAnimations();
    initFormValidation();
    initQuizOptions();
    initContactForm();
    initSpeakingPractice();
    handleXPandQuizFeedback();
});

function initTooltips() {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
    document.querySelectorAll('[data-bs-toggle="popover"]').forEach(el => new bootstrap.Popover(el));
}

function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', e => {
            const target = document.querySelector(anchor.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
}

function initAutoDismissAlerts() {
    document.querySelectorAll('.alert:not(.alert-permanent)').forEach(alert => {
        setTimeout(() => {
            const instance = bootstrap.Alert.getOrCreateInstance(alert);
            instance.close();
        }, 4500);
    });
}

function initButtons() {
    document.querySelectorAll('.btn').forEach(btn => {
        btn.addEventListener('click', e => {
            const ripple = document.createElement('span');
            ripple.classList.add('ripple');
            const rect = btn.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            ripple.style.width = ripple.style.height = `${size}px`;
            ripple.style.left = `${e.clientX - rect.left - size / 2}px`;
            ripple.style.top = `${e.clientY - rect.top - size / 2}px`;
            btn.appendChild(ripple);
            setTimeout(() => ripple.remove(), 600);
        });
    });
}

function initObserverAnimations() {
    const animateItems = document.querySelectorAll('.fade-up, .fade-left, .fade-right, .scale-in');
    if (!animateItems.length) return;
    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.2 });
    animateItems.forEach(el => observer.observe(el));
}

function initFormValidation() {
    document.querySelectorAll('.needs-validation').forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
}

function initQuizOptions() {
    const quizForm = document.getElementById('quizForm');
    if (!quizForm) return;
    quizForm.querySelectorAll('input[type="radio"]').forEach(radio => {
        radio.addEventListener('change', () => {
            quizForm.querySelectorAll('.quiz-option').forEach(opt => opt.classList.remove('selected'));
            radio.closest('.quiz-option')?.classList.add('selected');
        });
    });
}

function initContactForm() {
    const contactForm = document.getElementById('contactForm');
    if (!contactForm) return;
    contactForm.addEventListener('submit', event => {
        if (!contactForm.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        } else {
            const button = contactForm.querySelector('button[type="submit"]');
            if (button) {
                button.disabled = true;
                button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending...';
            }
        }
        contactForm.classList.add('was-validated');
    });
}

function handleXPandQuizFeedback() {
    const params = new URLSearchParams(window.location.search);
    if (params.get('completed') === '1') {
        showXPAnimation(10);
    }
    if (params.get('quiz_result')) {
        showQuizResult(params.get('quiz_result') === 'correct', params.get('score'), params.get('max'));
    }
}

function showXPAnimation(xp) {
    const xpElement = document.createElement('div');
    xpElement.className = 'xp-animation';
    xpElement.textContent = `+${xp} XP`;
    xpElement.style.cssText = `
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 3rem;
        font-weight: 700;
        color: #22c55e;
        z-index: 9999;
        pointer-events: none;
        animation: xpFloat 2s ease-out forwards;
    `;
    document.body.appendChild(xpElement);
    setTimeout(() => xpElement.remove(), 2000);
}

const style = document.createElement('style');
style.textContent = `
    @keyframes xpFloat {
        0% { opacity: 0; transform: translate(-50%, -50%) scale(0.75); }
        40% { opacity: 1; transform: translate(-50%, -70%) scale(1.1); }
        100% { opacity: 0; transform: translate(-50%, -90%) scale(1); }
    }
    .quiz-option.selected {
        border-color: #6366f1;
        background: rgba(99,102,241,0.12);
    }
`;
document.head.appendChild(style);

function showQuizResult(isCorrect, score, max) {
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${isCorrect ? 'success' : 'danger'} border-0`;
    toast.setAttribute('role', 'alert');
    toast.style.minWidth = '320px';
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <strong>${isCorrect ? 'Correct! 🎉' : 'Keep practicing 😌'}</strong><br>
                You scored ${score} out of ${max} points.${isCorrect ? ` Earned ${score} XP!` : ''}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    const container = document.getElementById('toast-container') || createToastContainer();
    container.appendChild(toast);
    new bootstrap.Toast(toast, { delay: 4000 }).show();
}

function initSpeakingPractice() {
    const widget = document.getElementById('speaking-practice');
    if (!widget) return;
    if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
        widget.innerHTML = '<p class="text-muted">Speech recognition is not supported in your browser.</p>';
        return;
    }
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    const recognition = new SpeechRecognition();
    recognition.lang = 'en-US';
    const button = widget.querySelector('#start-speaking');
    const resultDiv = widget.querySelector('#speaking-result');
    const expectedText = widget.dataset.expected || '';

    button?.addEventListener('click', () => {
        recognition.start();
        button.disabled = true;
        button.textContent = 'Listening...';
        resultDiv.textContent = 'Listening...';
    });

    recognition.onresult = event => {
        const transcript = event.results[0][0].transcript;
        resultDiv.textContent = `You said: "${transcript}"`;
        const similarity = calculateSimilarity(transcript.toLowerCase(), expectedText.toLowerCase());
        const score = Math.round(similarity * 100);
        let feedback = '';
        if (score >= 75) feedback = `<span class="text-success">Great! ${score}% match 🎯</span>`;
        else if (score >= 50) feedback = `<span class="text-warning">Nice effort! ${score}% match 💪</span>`;
        else feedback = `<span class="text-danger">Keep practicing! ${score}% match 🔁</span>`;
        resultDiv.innerHTML += `<br>${feedback}`;
        button.disabled = false;
        button.textContent = 'Start Speaking';
    };

    recognition.onerror = () => {
        resultDiv.textContent = 'Something went wrong. Please try again.';
        button.disabled = false;
        button.textContent = 'Start Speaking';
    };

    recognition.onend = () => {
        if (button?.disabled) {
            button.disabled = false;
            button.textContent = 'Start Speaking';
        }
    };
}

function calculateSimilarity(str1, str2) {
    const longer = str1.length > str2.length ? str1 : str2;
    const shorter = str1.length > str2.length ? str2 : str1;
    if (!longer.length) return 1;
    const distance = levenshteinDistance(longer, shorter);
    return (longer.length - distance) / longer.length;
}

function levenshteinDistance(a, b) {
    const matrix = Array.from({ length: b.length + 1 }, () => []);
    for (let i = 0; i <= b.length; i++) matrix[i][0] = i;
    for (let j = 0; j <= a.length; j++) matrix[0][j] = j;
    for (let i = 1; i <= b.length; i++) {
        for (let j = 1; j <= a.length; j++) {
            matrix[i][j] = Math.min(
                matrix[i - 1][j] + 1,
                matrix[i][j - 1] + 1,
                matrix[i - 1][j - 1] + (b.charAt(i - 1) === a.charAt(j - 1) ? 0 : 1)
            );
        }
    }
    return matrix[b.length][a.length];
}

function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'toast-container position-fixed top-0 end-0 p-3';
    container.style.zIndex = '9999';
    document.body.appendChild(container);
    return container;
}

window.IELab = {
    showToast: (message, type = 'info') => {
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>`;
        const container = document.getElementById('toast-container') || createToastContainer();
        container.appendChild(toast);
        new bootstrap.Toast(toast, { delay: 4000 }).show();
    },
    showXPAnimation
};
