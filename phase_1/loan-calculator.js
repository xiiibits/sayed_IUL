document.addEventListener('DOMContentLoaded', function() {
    const calculatorForm = document.getElementById('loan-calculator-form');
    if (!calculatorForm) return;
    
    calculatorForm.addEventListener('submit', function(e) {
        e.preventDefault();
        calculateLoan();
    });
    
    function calculateLoan() {
        const loanAmount = parseFloat(document.getElementById('loan-amount').value);
        const interestRate = parseFloat(document.getElementById('interest-rate').value) / 100 / 12;
        const loanTerm = parseInt(document.getElementById('loan-term').value) * 12;
        
        if (isNaN(loanAmount) || isNaN(interestRate) || isNaN(loanTerm)) {
            showError('Please enter valid numbers for all fields');
            return;
        }
        
        const x = Math.pow(1 + interestRate, loanTerm);
        const monthlyPayment = (loanAmount * x * interestRate) / (x - 1);
        
        const totalPayment = monthlyPayment * loanTerm;
        const totalInterest = totalPayment - loanAmount;
        
        document.getElementById('monthly-payment').textContent = formatCurrency(monthlyPayment);
        document.getElementById('total-payment').textContent = formatCurrency(totalPayment);
        document.getElementById('total-interest').textContent = formatCurrency(totalInterest);
        
        document.getElementById('loan-result').style.display = 'block';
    }
    
    function formatCurrency(value) {
        return '$' + value.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }
    
    function showError(message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.style.color = 'red';
        errorDiv.style.marginTop = '10px';
        errorDiv.appendChild(document.createTextNode(message));
        
        const resultDiv = document.getElementById('loan-result');
        calculatorForm.insertBefore(errorDiv, resultDiv);
        
        setTimeout(() => {
            document.querySelector('.error-message').remove();
        }, 3000);
    }
    
    const inputs = document.querySelectorAll('#loan-calculator-form input');
    inputs.forEach(input => {
        input.addEventListener('input', debounce(function() {
            if (allFieldsHaveValues()) {
                calculateLoan();
            }
        }, 500));
    });
    
    function allFieldsHaveValues() {
        return Array.from(inputs).every(input => input.value.trim() !== '');
    }
    
    function debounce(func, wait) {
        let timeout;
        return function() {
            const context = this;
            const args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                func.apply(context, args);
            }, wait);
        };
    }
    
    document.getElementById('loan-term').value = '4';
});