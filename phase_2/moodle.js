document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('login-form');
    const signupForm = document.getElementById('signup-form');
    const showSignupBtn = document.getElementById('show-signup-btn');
    const cancelBtn = document.getElementById('cancel-btn');
    const loginSection = document.querySelector('.login-section');
    const signupBox = document.getElementById('signup-box');
    
    if (showSignupBtn) {
        showSignupBtn.addEventListener('click', function() {
            loginSection.style.display = 'none';
            signupBox.style.display = 'block';
        });
    }
    
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function() {
            signupBox.style.display = 'none';
            loginSection.style.display = 'block';
        });
    }
    
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            window.location.href = 'success.html';
        });
    }
    
    if (signupForm) {
        signupForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const password = document.getElementById('new-password').value;
            const email = document.getElementById('email').value;
            const emailAgain = document.getElementById('email-again').value;
            
            if (email !== emailAgain) {
                alert('Email addresses do not match!');
                return;
            }
            
            const passwordRegex = /^(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])(?=.*[*\-#@$!%^&(){}[\]:;<>,.?/~_+\\|])[a-zA-Z0-9*\-#@$!%^&(){}[\]:;<>,.?/~_+\\|]{8,}$/;
            
            if (!passwordRegex.test(password)) {
                alert('Password does not meet the requirements.');
                return;
            }
            
            window.location.href = 'success.html';
        });
    }
});