import {useParams, useNavigate, Link} from "react-router-dom";

const mockPosts = [
    {
        id: 1,
        title: "Jakie gry polecacie na 2025?",
        author: "Janek123",
        created_at: "2025-06-01",
    },
    {
        id: 2,
        title: "Nowy RTX czy lepiej poczekać?",
        author: "GamerX",
        created_at: "2025-05-30",
    },
];

export default function TopicPage() {
    const { id } = useParams();
    const navigate = useNavigate();

    return (
        <div className="max-w-4xl mx-auto mt-10 px-4">
            <button
                onClick={() => navigate(-1)}
                className="text-blue-600 hover:text-blue-800 transition text-lg mb-4"
                title="Wróć"
            >
                ← Back
            </button>

            <h1 className="text-2xl font-bold mb-6 text-gray-800">
                Threads #{id}
            </h1>

            <div className="space-y-4">
                {mockPosts.map((post) => (
                    <Link
                        key={post.id}
                        to={`/post/${post.id}`}
                        className="block p-4 border rounded-lg bg-white shadow-sm hover:shadow transition"
                    >
                        <h2 className="text-lg font-semibold text-blue-700">{post.title}</h2>
                        <div className="text-sm text-gray-500 mt-1">
                            by <Link
                            to={`/user/${post.id || 1}`}
                            className="text-blue-600 hover:underline"
                        >
                            {post.author}
                        </Link>{" "} • {post.created_at}
                        </div>
                    </Link>
                ))}
            </div>
        </div>
    );
}
