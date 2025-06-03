import { useParams } from "react-router-dom";
import { useQuery } from "@tanstack/react-query";
import BackButton from "../components/BackButton";


const fetchUserInfo = async (uid) => {
    const res = await fetch("http://localhost:8080/api/v1/user/get", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        credentials: "include",
        body: JSON.stringify({ uid: Number(uid) }),
    });

    const data = await res.json();

    if (!res.ok) throw new Error(data.desc || "Failed to fetch user data");

    return data.value;
};

export default function UserPage() {
    const { id } = useParams();

    const {
        data: user,
        isLoading,
        isError,
        error,
    } = useQuery({
        queryKey: ["user", id],
        queryFn: () => fetchUserInfo(id),
    });

    return (
        <div className="max-w-4xl mx-auto mt-10 px-4 space-y-10">
            <BackButton />
            <div className="bg-white rounded-xl shadow p-6 border">
                {isLoading ? (
                    <div className="animate-pulse space-y-3">
                        <div className="h-6 bg-gray-200 rounded w-1/3"></div>
                        <div className="h-4 bg-gray-100 rounded w-2/3"></div>
                    </div>
                ) : isError ? (
                    <p className="text-red-500">Error: {error.message}</p>
                ) : (
                    <>
                        <h1 className="text-2xl font-bold text-blue-700 mb-2">
                            User: {user.nick}
                        </h1>
                        <p className="text-gray-600">
                            <span className="font-medium">Motto:</span> {user.motto}
                        </p>
                        <p className="text-gray-600">
                            <span className="font-medium">Provenance:</span> {user.provenance}
                        </p>
                    </>
                )}
            </div>
        </div>
    );
}
