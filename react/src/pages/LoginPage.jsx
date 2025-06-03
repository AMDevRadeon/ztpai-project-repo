import { useState } from "react";
import { useMutation } from "@tanstack/react-query";
import { loginUser } from "../api/auth";
import { useNavigate } from "react-router-dom";

export default function LoginPage() {
    const navigate = useNavigate();
    const [formData, setFormData] = useState({ email: "", password: "" });

    const mutation = useMutation({
        mutationFn: loginUser,
        onSuccess: () => {
            navigate("/");
        },
    });

    const handleChange = (e) => {
        setFormData((prev) => ({
            ...prev,
            [e.target.name]: e.target.value,
        }));
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        mutation.mutate(formData);
    };

    return (
        <div className="max-w-md mx-auto mt-20 p-6 bg-white rounded-2xl shadow">
            <h1 className="text-2xl font-bold mb-4 text-center">Log in</h1>

            <form onSubmit={handleSubmit} className="space-y-4">
                <div>
                    <label>Email</label>
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
                    <label className="block mb-1 font-medium">Password</label>
                    <input
                        type="password"
                        name="password"
                        className="w-full px-4 py-2 border rounded-lg"
                        value={formData.password}
                        onChange={handleChange}
                        required
                    />
                </div>

                {mutation.isError && (
                    <div className="text-red-600 text-sm">
                        {mutation.error.message || "Login failed"}
                    </div>
                )}

                <button
                    type="submit"
                    className="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition"
                    disabled={mutation.isLoading}
                >
                    {mutation.isLoading ? "Logging in..." : "Log in"}
                </button>
            </form>
        </div>
    );
}
