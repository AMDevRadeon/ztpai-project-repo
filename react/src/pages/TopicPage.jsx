import { useParams, useNavigate, Link, useLocation } from "react-router-dom";
import { useQuery } from "@tanstack/react-query";
import BackButton from "../components/BackButton";

const fetchPosts = async (tid) => {
    const res = await fetch("http://localhost:8080/api/v1/post/get", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        credentials: "include",
        body: JSON.stringify({
            tid: Number(tid),
            offset: 0,
            limit: 10,
        }),
    });

    const data = await res.json();

    if (!res.ok) {
        throw new Error(data.desc || "Failed to fetch posts");
    }

    return data.value.posts;
};

export default function TopicPage() {
    const { id } = useParams();
    const navigate = useNavigate();
    const { state } = useLocation();
    const topicTitle = state?.title ?? `Topic #${id}`;

    const {
        data: posts,
        isLoading,
        isError,
        error,
    } = useQuery({
        queryKey: ["posts", id],
        queryFn: () => fetchPosts(id),
    });

    return (
        <div className="max-w-4xl mx-auto mt-10 px-4">
            <BackButton />

            <h1 className="text-2xl font-bold mb-6 text-gray-800">{topicTitle}</h1>

            {isLoading && (
                <div className="space-y-4 animate-pulse">
                    {Array.from({ length: 3 }).map((_, i) => (
                        <div key={i} className="p-4 border rounded-lg bg-gray-100">
                            <div className="h-5 bg-gray-300 rounded w-1/2 mb-2" />
                            <div className="h-4 bg-gray-200 rounded w-1/4" />
                        </div>
                    ))}
                </div>
            )}

            {isError && <p className="text-red-500">Error: {error.message}</p>}

            {!isLoading && posts?.length === 0 && (
                <p className="text-gray-500">No posts found for this topic.</p>
            )}

            <div className="space-y-4">
                {posts?.map((post) => (
                    <Link
                        key={post.pid}
                        to={`/post/${post.pid}`}
                        state={{ post }}
                        className="block p-4 border rounded-lg bg-white shadow-sm hover:shadow transition"
                    >
                        <h2 className="text-lg font-semibold text-blue-700">
                            {post.title}
                        </h2>
                        <div className="text-sm text-gray-500 mt-1">
                            by{" "}
                            <Link
                                to={`/user/${post.uid}`}
                                className="text-blue-600 hover:underline"
                            >
                                User#{post.uid}
                            </Link>{" "}
                            •{" "}
                            {new Date(post.postCreationTimestamp).toLocaleString()}
                        </div>
                    </Link>
                ))}
            </div>
        </div>
    );
}
