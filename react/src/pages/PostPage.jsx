import { useParams, useNavigate, Link, useLocation } from "react-router-dom";
import { useState } from "react";
import { useQuery } from "@tanstack/react-query";
import BackButton from "../components/BackButton";

// Comments API call
const fetchComments = async (pid, offset, limit) => {
    const res = await fetch("http://localhost:8080/api/v1/comment/get", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        credentials: "include",
        body: JSON.stringify({
            pid: Number(pid),
            offset,
            limit,
        }),
    });

    const data = await res.json();
    if (!res.ok) throw new Error(data.desc || "Failed to fetch comments");

    return data.value.comments;
};

export default function PostPage() {
    const [isSubmitting, setIsSubmitting] = useState(false);
    const { id } = useParams(); // pid
    const navigate = useNavigate();
    const location = useLocation();
    const { post } = location.state || {}; // coming from <Link state={{ post }} />
    const [comment, setComment] = useState("");
    const [visibleCount, setVisibleCount] = useState(5);

    const {
        data: comments,
        isLoading: commentsLoading,
        isError: commentsError,
        error: commentsErrorData,
        refetch,
    } = useQuery({
        queryKey: ["comments", id],
        queryFn: () => fetchComments(id, 0, 20),
    });


    const handleLoadMore = () => {
        setVisibleCount((prev) => prev + 5);
    };

    const handleCommentSubmit = async (e) => {
        e.preventDefault();
        setIsSubmitting(true);

        try {
            const res = await fetch("http://localhost:8080/api/v1/comment/add", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                credentials: "include",
                body: JSON.stringify({
                    pid: Number(id),
                    content: comment,
                }),
            });

            const data = await res.json();
            if (!res.ok) {
                throw new Error(data.desc || "Failed to add comment");
            }

            setComment(""); // wyczyść pole
            await refetch(); // odśwież komentarze
        } catch (error) {
            alert(`Failed to add comment: ${error.message}`);
        } finally {
            setIsSubmitting(false);
        }
    };


    const visibleComments = comments?.slice(0, visibleCount) || [];
    const hasMore = comments && visibleCount < comments.length;

    return (
        <div className="max-w-3xl mx-auto mt-10 px-4 space-y-10">
            <BackButton />
            <div className="bg-white p-6 rounded-xl shadow border">

                {!post ? (
                    <p className="text-red-500">Error: Post not found</p>
                ) : (
                    <>
                        <h1 className="text-2xl font-bold text-blue-700">{post.title}</h1>
                        <p className="text-gray-700 mt-2 whitespace-pre-line">{post.content}</p>
                        <div className="text-sm text-gray-500 mt-3">
                            By{" "}
                            <Link
                                to={`/user/${post.uid}`}
                                className="text-blue-600 hover:underline"
                            >
                                User#{post.uid}
                            </Link>{" "}
                            • {new Date(post.postCreationTimestamp).toLocaleString()}
                        </div>
                    </>
                )}
            </div>

            {/* Add comment */}
            <form
                onSubmit={handleCommentSubmit}
                className="bg-white border rounded-xl shadow p-4"
            >
                <label className="block mb-2 font-medium text-gray-700">Add comment:</label>
                <textarea
                    value={comment}
                    onChange={(e) => setComment(e.target.value)}
                    className="w-full border rounded-lg px-4 py-2"
                    rows={4}
                    required
                />
                <button
                    type="submit"
                    disabled={isSubmitting}
                    className="mt-4 bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition disabled:opacity-50"
                >
                    {isSubmitting ? "Sending..." : "Send"}
                </button>

            </form>

            {/* Comments */}
            <div className="space-y-4">
                <h2 className="text-lg font-semibold text-gray-800">Comments</h2>

                {commentsLoading ? (
                    <div className="space-y-2 animate-pulse">
                        {[...Array(3)].map((_, i) => (
                            <div key={i} className="h-4 bg-gray-200 rounded w-3/4" />
                        ))}
                    </div>
                ) : commentsError ? (
                    <p className="text-red-500">Error: {commentsErrorData.message}</p>
                ) : visibleComments.length === 0 ? (
                    <p className="text-gray-500">No comments yet.</p>
                ) : (
                    visibleComments.map((com) => (
                        <div key={com.cid} className="bg-gray-100 rounded-lg p-4 border">
                            <p className="text-gray-700">{com.content}</p>
                            <div className="text-sm text-gray-500 mt-1">
                                <Link
                                    to={`/user/${com.uid}`}
                                    className="text-blue-600 hover:underline"
                                >
                                    User#{com.uid}
                                </Link>{" "}
                                • {new Date(com.commentCreationTimestamp).toLocaleString()}
                            </div>
                        </div>
                    ))
                )}

                {hasMore && (
                    <button
                        onClick={handleLoadMore}
                        className="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                    >
                        Load more
                    </button>
                )}
            </div>
        </div>
    );
}
