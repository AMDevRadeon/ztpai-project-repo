import {useParams, useNavigate, Link} from "react-router-dom";
import { useState } from "react";

const mockPost = {
    id: 1,
    title: "Jakie gry polecacie na 2025?",
    content: "Szukam czegoś nowego do grania na wakacje. Co polecacie?",
    author: "Janek123",
    created_at: "2025-06-01",
};

const mockComments = [
    { id: 1, author: "GamerX", text: "Polecam Baldur's Gate 4!", created_at: "2025-06-01" },
    { id: 2, author: "Marta", text: "Nowy Frostpunk też ma wyjść w lipcu.", created_at: "2025-06-02" },
    { id: 3, author: "Kamil", text: "Czekam na Cyberpunk 2!", created_at: "2025-06-02" },
    { id: 4, author: "Julia", text: "Valorant wciąga...", created_at: "2025-06-03" },
    { id: 5, author: "Oskar", text: "Wiedźmin 4?", created_at: "2025-06-03" },
    { id: 6, author: "Ewa", text: "Hades II na pewno!", created_at: "2025-06-04" },
    { id: 7, author: "Marta", text: "Nowy Frostpunk też ma wyjść w lipcu.", created_at: "2025-06-02" },
    { id: 8, author: "Marta", text: "Nowy Frostpunk też ma wyjść w lipcu.", created_at: "2025-06-02" },
    { id: 9, author: "Marta", text: "Nowy Frostpunk też ma wyjść w lipcu.", created_at: "2025-06-02" },
    { id: 10, author: "Marta", text: "Nowy Frostpunk też ma wyjść w lipcu.", created_at: "2025-06-02" },
    { id: 11, author: "Marta", text: "Nowy Frostpunk też ma wyjść w lipcu.", created_at: "2025-06-02" },
];

export default function PostPage() {
    const { id } = useParams();
    const [comment, setComment] = useState("");
    const navigate = useNavigate();

    const [visibleCount, setVisibleCount] = useState(5);

    const visibleComments = mockComments.slice(0, visibleCount);
    const hasMore = visibleCount < mockComments.length;

    const handleLoadMore = () => {
        setVisibleCount((prev) => prev + 5);
    };


    const handleCommentSubmit = (e) => {
        e.preventDefault();
        // TODO: call API to submit comment
        console.log("Wysyłanie komentarza:", comment);
        setComment("");
    };

    return (
        <div className="max-w-3xl mx-auto mt-10 px-4 space-y-10">
            {/* Post content */}
            <div className="bg-white p-6 rounded-xl shadow border">
                <button
                    onClick={() => navigate(-1)}
                    className="text-blue-600 hover:text-blue-800 transition text-lg mb-4"
                    title="Wróć"
                >
                    ← Back
                </button>
                {/* Post title */}
                <h1 className="text-2xl font-bold text-blue-700">{mockPost.title}</h1>
                <p className="text-gray-700 mt-2 whitespace-pre-line">{mockPost.content}</p>
                <div className="text-sm text-gray-500 mt-3">
                    By <Link
                    to={`/user/${mockPost.id || 1}`}
                    className="text-blue-600 hover:underline"
                >
                    {mockPost.author}
                </Link>{" "} • {mockPost.created_at}
                </div>
            </div>

            {/* Add comment */}
            <form onSubmit={handleCommentSubmit} className="bg-white border rounded-xl shadow p-4">
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
                    className="mt-4 bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition"
                >
                    Send
                </button>
            </form>

            {/* Comments */}
            <div className="space-y-4">
                <h2 className="text-lg font-semibold text-gray-800">Comments</h2>

                {visibleComments.map((com) => (
                    <div key={com.id} className="bg-gray-100 rounded-lg p-4 border">
                        <p className="text-gray-700">{com.text}</p>
                        <div className="text-sm text-gray-500 mt-1">
                            <Link
                                to={`/user/${com.id || 1}`}
                                className="text-blue-600 hover:underline"
                            >
                                {com.author}
                            </Link>{" "}
                            • {com.created_at}
                        </div>

                    </div>
                ))}

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
