import React from 'react';
import { useQuery } from '@tanstack/react-query';
import { Link } from 'react-router-dom';

const fetchTopics = async () => {
    const res = await fetch('http://localhost:8080/api/v1/topic/get', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            offset: 0,
            limit: 10,
        }),
        credentials: 'include',
    });

    const data = await res.json();

    if (!res.ok) {
        throw new Error(data.desc || 'Failed to fetch topics');
    }

    return data.value.topics;
};

const mockTopics = [
    {
        id: 1,
        name: "Gry komputerowe",
        description: "Dyskusje o grach, sprzęcie i wszystkim co związane z gamingiem.",
    },
    {
        id: 2,
        name: "Programowanie",
        description: "Tematy związane z kodowaniem, frameworkami i technologiami.",
    },
];

const HomePage = () => {
    const { data, isLoading, isError, error } = useQuery({
        queryKey: ['topics'],
        queryFn: fetchTopics,
    });

    return (
        <div className="max-w-4xl mx-auto px-4 py-8">
            <h1 className="text-3xl font-bold mb-6">Forum Topics</h1>

            {isLoading && (
                <div className="space-y-4 animate-pulse">
                    {Array.from({ length: 3 }).map((_, idx) => (
                        <div
                            key={idx}
                            className="p-4 border rounded-xl bg-gray-100"
                        >
                            <div className="h-6 bg-gray-300 rounded w-1/3 mb-2"></div>
                            <div className="h-4 bg-gray-200 rounded w-1/2"></div>
                        </div>
                    ))}
                </div>
            )}

            {isError && <p className="text-red-500">Error: {error.message}</p>}
            {!isLoading && data?.length === 0 && (
                <p className="text-gray-500">No topics found.</p>
            )}

            <div className="space-y-4">
                {data?.map((topic) => (
                    <Link
                        key={topic.tid}
                        to={`/topic/${topic.tid}`}
                        state={{ title: topic.title }}
                        className="block p-4 border rounded-xl hover:bg-gray-50 transition"
                    >
                        <h2 className="text-xl font-semibold">{topic.title}</h2>
                        <p className="text-sm text-gray-500">
                            Created on{' '}
                            {new Date(topic.topicCreationTimestamp).toLocaleString()}
                        </p>
                    </Link>
                ))}
            </div>
        </div>
    );
};


export default HomePage;

/*
export default function HomePage() {
    return (
        <div className="max-w-4xl mx-auto mt-10 px-4">
            <h1 className="text-3xl font-bold mb-6 text-gray-800">Forum Topics</h1>

            <div className="grid gap-6">
                {mockTopics.map((topic) => (
                    <Link
                        key={topic.id}
                        to={`/topic/${topic.id}`}
                        className="block p-6 rounded-xl border border-gray-200 shadow-sm bg-white hover:shadow-md transition"
                    >
                        <h2 className="text-xl font-semibold text-blue-600">{topic.name}</h2>
                        <p className="text-gray-600 mt-1">{topic.description}</p>
                    </Link>
                ))}
            </div>
        </div>
    );
}
 */
