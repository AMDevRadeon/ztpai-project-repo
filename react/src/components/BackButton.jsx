import { useNavigate } from "react-router-dom";
import { ArrowLeft } from "lucide-react";

export default function BackButton({ className = "" }) {
    const navigate = useNavigate();

    return (
        <button
            onClick={() => navigate(-1)}
            className={`flex items-center text-blue-600 hover:text-blue-800 transition text-lg ${className}`}
            title="Back"
        >
            <ArrowLeft className="w-5 h-5 mr-1" />
            Back
        </button>
    );
}
