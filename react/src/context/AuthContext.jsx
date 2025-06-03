import { createContext, useContext, useEffect, useState } from "react";

const AuthContext = createContext();

export const AuthProvider = ({ children }) => {
    const [isAuthenticated, setIsAuthenticated] = useState(null); // null = unknown
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    // Sprawdzanie sesji
    const checkSession = async () => {
        try {
            const res = await fetch("http://localhost:8080/api/v1/token_refresh", {
                method: "POST",
                credentials: "include",
            });

            if (res.ok) {
                setIsAuthenticated(true);
                console.log("Działa");
                setError(null);
            } else {
                setIsAuthenticated(false);
            }
        } catch (err) {
            console.error("Session check failed:", err);
            setIsAuthenticated(false);
            setError("Session check failed");
        } finally {
            setLoading(false);
        }
    };

    // Wylogowanie
    const logout = async () => {
        try {
            await fetch("http://localhost:8080/api/v1/token_invalidate", {
                method: "POST",
                credentials: "include",
            });
        } catch (err) {
            console.error("Logout failed:", err);
        } finally {
            setIsAuthenticated(false);
        }
    };

    useEffect(() => {
        checkSession();
    }, []);

    return (
        <AuthContext.Provider
            value={{
                isAuthenticated,
                loading,
                error,
                logout,
                checkSession,
            }}
        >
            {children}
        </AuthContext.Provider>
    );
};

// Hook do używania w komponentach
export const useAuth = () => useContext(AuthContext);
