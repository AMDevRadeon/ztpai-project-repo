import { useParams } from "react-router-dom";

const mockUser = {
    id: 1,
    username: "Janek123",
    joined: "2024-08-15",
    posts: [
        { id: 1, title: "Best games of 2025?", created_at: "2025-06-01" },
        { id: 2, title: "Should I buy a new GPU?", created_at: "2025-05-30" },
    ],
    commentsCount: 14,
};

export default function UserPage() {
    const { id } = useParams();

    return (
        <div className="max-w-4xl mx-auto mt-10 px-4 space-y-10">
            <div className="bg-white rounded-xl shadow p-6 border">
                <h1 className="text-2xl font-bold text-blue-700 mb-2">
                    User: {mockUser.username}
                </h1>
                <p className="text-gray-600">Joined: {mockUser.joined}</p>
                <p className="text-gray-600">Total comments: {mockUser.commentsCount}</p>
            </div>

            <div>
                <h2 className="text-lg font-semibold text-gray-800 mb-4">User's Threads</h2>
                <div className="space-y-3">
                    {mockUser.posts.map((post) => (
                        <div
                            key={post.id}
                            className="p-4 bg-white border rounded-lg shadow-sm hover:shadow transition"
                        >
                            <h3 className="text-blue-700 font-semibold">{post.title}</h3>
                            <div className="text-sm text-gray-500">Posted on {post.created_at}</div>
                        </div>
                    ))}
                </div>
            </div>
        </div>
    );
}