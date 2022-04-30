document
.addEventListener('DOMContentLoaded', () => {

    const button = document.getElementById('download-button');

    button.addEventListener('click', async e => {

        e.preventDefault();
        
        const a = document.createElement('a');

        a.href = '/flow/orders_to_csv';
        a.click();
        
    });

});