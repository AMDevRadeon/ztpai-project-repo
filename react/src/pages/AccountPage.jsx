import { useEffect, useState } from "react";
import { useMutation, useQuery } from "@tanstack/react-query";
import { useAuth } from "../context/AuthContext";

const fetchUser = async () => {
    const res = await fetch("http://localhost:8080/api/v1/user/get", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        credentials: "include",
        body: JSON.stringify({ uid: 5 }),
    });

    const data = await res.json();
    if (!res.ok) throw new Error(data.desc || "Failed to fetch user data");
    return data.value;
};

const updateUser = async (data) => {
    const res = await fetch("http://localhost:8080/api/v1/user/me", {
        method: "PATCH",
        headers: { "Content-Type": "application/json" },
        credentials: "include",
        body: JSON.stringify(data),
    });

    const result = await res.json();
    if (!res.ok) throw new Error(result.desc || "Failed to update account");
    return result;
};

export default function AccountPage() {
    const { user: authUser } = useAuth();

    const { data: user, isLoading, isError, error } = useQuery({
        queryKey: ["my-account", 5],
        queryFn: fetchUser,
    });

    const [formData, setFormData] = useState({
        motto: "",
        provenance: "",
        password: "",
    });

    const mutation = useMutation({
        mutationFn: updateUser,
        onSuccess: () => alert("Account updated!"),
    });

    useEffect(() => {
        if (user) {
            setFormData({
                motto: user.motto || "",
                provenance: user.provenance || "",
                password: "",
            });
        }
    }, [user]);

    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData((prev) => ({ ...prev, [name]: value }));
    };

    const handleSubmit = (e) => {
        e.preventDefault();

        const payload = {
            motto: formData.motto,
            provenance: formData.provenance,
        };

        if (formData.password.trim() !== "") {
            payload.password = formData.password;
        }

        mutation.mutate(payload);
    };


    if (!authUser) {
        return (
            <div className="max-w-xl mx-auto mt-10 p-6 bg-white rounded-xl shadow border">
                <h1 className="text-2xl font-bold text-blue-700 mb-4">My Account</h1>
                <p className="text-gray-500">You must be logged in to view this page.</p>
            </div>
        );
    }

    return (
        <div className="max-w-xl mx-auto mt-10 p-6 bg-white rounded-xl shadow border">
            <h1 className="text-2xl font-bold text-blue-700 mb-4">My Account</h1>

            {isLoading && <p className="text-gray-500">Loading...</p>}
            {isError && <p className="text-red-500">Error: {error.message}</p>}

            {!isLoading && user && (
                <form onSubmit={handleSubmit} className="space-y-4">
                    <div>
                        <label className="block mb-1 font-medium">Nickname</label>
                        <input
                            type="text"
                            value={user.nick}
                            disabled
                            className="w-full px-4 py-2 border rounded-lg bg-gray-100"
                        />
                    </div>

                    <div>
                        <label className="block mb-1 font-medium">Motto</label>
                        <input
                            type="text"
                            name="motto"
                            value={formData.motto}
                            onChange={handleChange}
                            className="w-full px-4 py-2 border rounded-lg"
                        />
                    </div>

                    <div>
                        <label className="block mb-1 font-medium">Provenance</label>
                        <input
                            type="text"
                            name="provenance"
                            value={formData.provenance}
                            onChange={handleChange}
                            className="w-full px-4 py-2 border rounded-lg"
                        />
                    </div>

                    <div>
                        <label className="block mb-1 font-medium">New Password</label>
                        <input
                            type="password"
                            name="password"
                            value={formData.password}
                            onChange={handleChange}
                            className="w-full px-4 py-2 border rounded-lg"
                        />
                    </div>

                    <button
                        type="submit"
                        disabled={mutation.isLoading}
                        className="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition"
                    >
                        {mutation.isLoading ? "Saving..." : "Save Changes"}
                    </button>

                    {mutation.isError && (
                        <p className="text-red-600 text-sm mt-2">
                            {mutation.error.message}
                        </p>
                    )}
                </form>
            )}
        </div>
    );
}
