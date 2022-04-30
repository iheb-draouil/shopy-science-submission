document
.addEventListener('DOMContentLoaded', () => {

    const errorPrompt = document.getElementById('error-prompt');
    
    const form = document.getElementById('login-form');

    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');

    if (!errorPrompt || !form || !usernameInput || !passwordInput) {
        throw new Error('Page elements not found');
    }

    form.addEventListener('submit', async e => {

        e.preventDefault();

        const response = await fetch('/api/login', {
            method: 'post',
            body: JSON.stringify({
                username: usernameInput.value,
                password: passwordInput.value,
            })
        });

        if (response.status == 200) {
            window.location.assign('/flow/retreive-untreated-orders');
        }

        else if (response.headers.get('content-type') == 'application/json') {
            const body = await response.json();
            errorPrompt.style.display = 'block';
            errorPrompt.value = body.error;
        }

        else {
            console.log(response.status);
            errorPrompt.style.display = 'block';
            errorPrompt.value = 'Something went wrong';
        }

    });

});