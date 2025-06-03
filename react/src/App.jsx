import { Routes, Route, Navigate } from 'react-router-dom';
import HomePage from './pages/HomePage';
import LoginPage from './pages/LoginPage';
import RegisterPage from './pages/RegisterPage';
import Navbar from './components/Navbar';
import Footer from './components/Footer';
import TopicPage from "./pages/TopicPage";
import PostPage from './pages/PostPage';
import UserPage from "./pages/UserPage";
import AccountPage from "./pages/AccountPage";

export default function App() {
    return (
        <>
            <div className="min-h-screen flex flex-col">
                <Navbar />
                    <main className="flex-1">
                        <Routes>
                            <Route path="/" element={<HomePage />} />
                            <Route path="/login" element={<LoginPage />} />
                            <Route path="/register" element={<RegisterPage />} />
                            <Route path="*" element={<Navigate to="/login" />} />
                            <Route path="/topic/:id" element={<TopicPage />} />
                            <Route path="/post/:id" element={<PostPage />} />
                            <Route path="/user/:id" element={<UserPage />} />
                            <Route path="/account" element={<AccountPage />} />
                        </Routes>
                    </main>
                <Footer />
            </div>
        </>
    );
}
