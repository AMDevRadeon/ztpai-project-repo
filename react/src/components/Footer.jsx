import React from "react";
import { Link } from "react-router-dom";

const Footer = () => {
    return (
        <footer className="bg-gray-100 border-t border-gray-200 mt-16">
            <div className="max-w-7xl mx-auto px-6 py-10">
                <div className="flex flex-col md:flex-row items-center justify-between space-y-4 md:space-y-0">
                    <p className="text-sm text-gray-500">
                        © {new Date().getFullYear()} ForumZone. All rights reserved.
                    </p>
                </div>
            </div>
        </footer>
    );
};

export default Footer;
