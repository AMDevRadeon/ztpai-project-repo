export async function loginUser(data) {
    const res = await fetch('http://localhost:8080/api/v1/login_check', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify(data),
    });

    const result = await res.json();

    if (!res.ok || !result.refresh_token) {
        throw new Error(result.message || 'Login failed');
    }

    localStorage.setItem('token', result.refresh_token);

    return result;
}


export async function registerUser(data) {
    const res = await fetch('http://localhost:8080/api/v1/register', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify(data),
    });

    const result = await res.json();

    if (!res.ok || result.code !== "200") {
        throw new Error(result.desc || 'Registration failed');
    }

    return result;
}


