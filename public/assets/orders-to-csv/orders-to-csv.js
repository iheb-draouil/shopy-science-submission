document
.addEventListener('DOMContentLoaded', () => {

    const downloadButton = document.getElementById('download-button');
    const logoutLink = document.getElementById('logout');

    if (!downloadButton || !logoutLink) {
        throw new Error('Page elements not found');
    }

    downloadButton.addEventListener('click', async e => {

        e.preventDefault();
        
        const a = document.createElement('a');

        a.href = '/flow/orders_to_csv';
        a.click();
        
    });

    logoutLink.addEventListener('click', async e => {

        e.preventDefault();

        const response = await fetch('/api/logout', { method: 'post' });

        if (response.status) {
            window.location.assign('/login');
        }

        else {
            throw new Error('Unexpected error');
        }

    });



});