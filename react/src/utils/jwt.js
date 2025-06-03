import { jwtDecode } from "jwt-decode";

export function getTokenFromCookie() {
    const match = document.cookie.match(/BEARER=([^;]+)/);
    return match?.[1] || null;
}

export function decodeUserFromToken() {
    const token = getTokenFromCookie();
    if (!token) return null;

    try {
        return jwtDecode(token);
    } catch (err) {
        console.error("Invalid JWT:", err);
        return null;
    }
}
