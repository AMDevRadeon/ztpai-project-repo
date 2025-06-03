import React from 'react';
import { Link } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

const Navbar = () => {
    const { isAuthenticated, logout, loading } = useAuth();

    return (
        <nav className="bg-gray-100 border-b border-gray-200 shadow-sm">
            <div className="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
                <Link to="/" className="text-2xl font-extrabold text-blue-600 tracking-tight">
                    ForumZone
                </Link>

                {!loading && (
                    <div className="space-x-4">
                        {isAuthenticated ? (
                            <>
                                <Link
                                    to="/account"
                                    className="px-4 py-2 rounded-xl text-sm font-medium text-gray-700 hover:bg-blue-100 transition"
                                >
                                    My Account
                                </Link>
                                <button
                                    onClick={logout}
                                    className="px-4 py-2 rounded-xl text-sm font-medium text-white bg-red-600 hover:bg-red-700 transition"
                                >
                                    Logout
                                </button>
                            </>
                        ) : (
                            <>
                                <Link
                                    to="/login"
                                    className="px-4 py-2 rounded-xl text-sm font-medium text-gray-700 hover:bg-blue-100 transition"
                                >
                                    Log in
                                </Link>
                                <Link
                                    to="/register"
                                    className="px-4 py-2 rounded-xl text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 transition"
                                >
                                    Register
                                </Link>
                            </>
                        )}
                    </div>
                )}
            </div>
        </nav>
    );
};

export default Navbar;
