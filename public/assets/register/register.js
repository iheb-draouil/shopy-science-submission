document
.addEventListener('DOMContentLoaded', () => {

    const errorPrompt = document.getElementById('error-prompt');
    
    const form = document.getElementById('register-form');

    const usernameInput = document.getElementById('username');
    const firstnameInput = document.getElementById('firstname');
    const lastnameInput = document.getElementById('lastname');
    const passwordInput = document.getElementById('password');
    const repasswordInput = document.getElementById('re-password');

    if (!form
        || !errorPrompt
        || !usernameInput
        || !passwordInput
        || !lastnameInput
        || !firstnameInput) {
        throw new Error('Page elements not found');
    }

    form.addEventListener('submit', async e => {

        e.preventDefault();

        if (passwordInput.value == repasswordInput.value) {

            const response = await fetch('/api/register', {
                method: 'post',
                body: JSON.stringify({
                    username: usernameInput.value,
                    first_name: firstnameInput.value,
                    last_name: lastnameInput.value,
                    password: passwordInput.value,
                })
            });
    
            if (response.status == 201) {
                window.location.assign('/login');
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

        }
    
        else {
            errorPrompt.style.display = 'block';
            errorPrompt.value = "Passwords don't match";
        }

    });

});