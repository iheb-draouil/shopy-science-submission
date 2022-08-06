import * as jose from './libraries/jose';

function refresh() {
        
    const response = await fetch('/api/refresh', { method: 'post' });

    if (response.status == 200) {
        const body = await response.json();
        localStorage.setItem('token', body.token);
        return body.token;
    }

    window.location.assign('/login');

}

export async function fetchAuth(url, { method, body }) {
    
    let accessToken = localStorage.getItem('token');

    if (accessToken === null) {
        accessToken = refresh();
    }
    
    try {

        const payload = jose.decodeJwt(accessToken);

        if (Date.now() > payload.exp) {
            accessToken = refresh();
        }

    }

    catch {
        accessToken = refresh();
    }
    
    const response = await fetch(url, { method, body, headers: {
        'Authorization': 'Bearer ' + accessToken
    }});
    
    if (response.status == 401) {
    
        return await fetch(url, { method, body, headers: {
            'Authorization': 'Bearer ' + refresh()
        }});

    }
    
    return response;
}