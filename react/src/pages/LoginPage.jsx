import { useState } from "react";
import { useMutation } from "@tanstack/react-query";
import { loginUser } from "../api/auth";
import { useNavigate } from "react-router-dom";
import { useAuth } from "../context/AuthContext";
import { decodeUserFromToken } from "../utils/jwt";

export default function LoginPage() {
    const navigate = useNavigate();
    const { setIsAuthenticated, setUser } = useAuth();

    const [formData, setFormData] = useState({ email: "", password: "" });

    const {
        mutate,
        isLoading,
        isError,
        error,
    } = useMutation({
        mutationFn: loginUser,
        onSuccess: () => {
            const user = decodeUserFromToken();
            if (user) {
                setUser(user);
                setIsAuthenticated(true);
                navigate("/");
            } else {
                console.error("Could not decode user from token");
            }
        },
        onError: (err) => {
            console.error("Login error:", err.message);
        },
    });

    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData((prev) => ({ ...prev, [name]: value }));
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        if (isLoading) return;
        mutate(formData);
    };

    return (
        <div className="max-w-md mx-auto mt-20 p-6 bg-white rounded-2xl shadow relative">
            <h1 className="text-2xl font-bold mb-4 text-center">Log in</h1>

            <form onSubmit={handleSubmit} className="space-y-4">
                <div>
                    <label className="block mb-1">Email</label>
                    <input
                        type="email"
                        name="email"
                        className="w-full px-4 py-2 border rounded-lg"
                        value={formData.email}
                        onChange={handleChange}
                        required
                    />
                </div>

                <div>
                    <label className="block mb-1">Password</label>
                    <input
                        type="password"
                        name="password"
                        className="w-full px-4 py-2 border rounded-lg"
                        value={formData.password}
                        onChange={handleChange}
                        required
                    />
                </div>

                {isError && (
                    <div className="text-red-600 text-sm">
                        {error.message || "Login failed"}
                    </div>
                )}

                <button
                    type="submit"
                    disabled={isLoading}
                    className={`w-full py-2 rounded-lg text-white transition ${
                        isLoading
                            ? "bg-blue-400 cursor-not-allowed"
                            : "bg-blue-600 hover:bg-blue-700"
                    }`}
                >
                    {isLoading ? "Logging in..." : "Log in"}
                </button>
            </form>
        </div>
    );
}