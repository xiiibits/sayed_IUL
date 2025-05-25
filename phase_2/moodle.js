// Updated moodle.js with PHP integration

document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('login-form');
    const signupForm = document.getElementById('signup-form');
    const showSignupBtn = document.getElementById('show-signup-btn');
    const cancelBtn = document.getElementById('cancel-btn');
    const loginSection = document.querySelector('.login-section');
    const signupBox = document.getElementById('signup-box');
    
    // Show/hide signup form
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
    
    // Handle login form submission
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            const submitBtn = loginForm.querySelector('.login-btn');
            
            // Validate input
            if (!username || !password) {
                showMessage('Please enter both username and password', 'error');
                return;
            }
            
            // Disable submit button and show loading
            submitBtn.disabled = true;
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Logging in...';
            
            // Send login request
            fetch('login_process.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    username: username,
                    password: password
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(data.message, 'success');
                    // Redirect to dashboard or success page
                    setTimeout(() => {
                        window.location.href = 'dashboard.php'; // Create this page
                    }, 1500);
                } else {
                    showMessage(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('An error occurred. Please try again.', 'error');
            })
            .finally(() => {
                // Re-enable submit button
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            });
        });
    }
    
    // Handle signup form submission
    if (signupForm) {
        signupForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = {
                username: document.getElementById('new-username').value.trim(),
                password: document.getElementById('new-password').value,
                email: document.getElementById('email').value.trim(),
                emailAgain: document.getElementById('email-again').value.trim(),
                firstName: document.getElementById('firstname').value.trim(),
                lastName: document.getElementById('lastname').value.trim(),
                city: document.getElementById('city').value.trim(),
                country: document.getElementById('country').value
            };
            
            const submitBtn = signupForm.querySelector('.create-btn');
            
            // Client-side validation
            if (!formData.username || !formData.password || !formData.email || 
                !formData.emailAgain || !formData.firstName || !formData.lastName) {
                showMessage('Please fill in all required fields', 'error');
                return;
            }
            
            if (formData.email !== formData.emailAgain) {
                showMessage('Email addresses do not match!', 'error');
                return;
            }
            
            // Password validation
            const passwordRegex = /^(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])(?=.*[*\-#@$!%^&(){}[\]:;<>,.?/~_+\\|])[a-zA-Z0-9*\-#@$!%^&(){}[\]:;<>,.?/~_+\\|]{8,}$/;
            if (!passwordRegex.test(formData.password)) {
                showMessage('Password does not meet the requirements.', 'error');
                return;
            }
            
            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(formData.email)) {
                showMessage('Please enter a valid email address', 'error');
                return;
            }
            
            // Disable submit button and show loading
            submitBtn.disabled = true;
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Creating Account...';
            
            // Send registration request
            fetch('register_process.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(data.message, 'success');
                    // Redirect to dashboard or success page
                    setTimeout(() => {
                        window.location.href = 'dashboard.php'; // Create this page
                    }, 1500);
                } else {
                    showMessage(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('An error occurred. Please try again.', 'error');
            })
            .finally(() => {
                // Re-enable submit button
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            });
        });
    }
    
    // Function to show messages
    function showMessage(message, type) {
        // Remove existing messages
        const existingMessages = document.querySelectorAll('.message');
        existingMessages.forEach(msg => msg.remove());
        
        // Create message element
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${type}`;
        messageDiv.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 5px;
            color: white;
            font-weight: bold;
            z-index: 1000;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transform: translateX(100%);
            transition: transform 0.3s ease;
        `;
        
        if (type === 'success') {
            messageDiv.style.backgroundColor = '#28a745';
        } else if (type === 'error') {
            messageDiv.style.backgroundColor = '#dc3545';
        } else {
            messageDiv.style.backgroundColor = '#17a2b8';
        }
        
        messageDiv.textContent = message;
        document.body.appendChild(messageDiv);
        
        // Animate in
        setTimeout(() => {
            messageDiv.style.transform = 'translateX(0)';
        }, 100);
        
        // Remove after 5 seconds
        setTimeout(() => {
            messageDiv.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (messageDiv.parentNode) {
                    messageDiv.parentNode.removeChild(messageDiv);
                }
            }, 300);
        }, 5000);
    }
});