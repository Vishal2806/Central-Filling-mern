import { BrowserRouter, Routes, Route, Navigate } from "react-router-dom";
import { useAuth } from "../context/AuthContext"; // Import your auth hook

import DashboardPage from "../pages/DashboardPage";
import RecordsPage from "../pages/RecordsPage";
import RecordDetailsPage from "../pages/RecordDetailsPage";
import AddRecordPage from "../pages/AddRecordPage";
import LoginPage from "../pages/LoginPage";
import ProtectedRoute from "../components/ProtectedRoute";

const AppRoutes = () => {
  const { token, loading } = useAuth();

  // Prevents the app from redirecting while reading localStorage on refresh
  if (loading) {
    return (
      <div style={{ 
        display: "flex", 
        justifyContent: "center", 
        alignItems: "center", 
        height: "100vh",
        fontFamily: "sans-serif"
      }}>
        <h3>Loading session...</h3>
      </div>
    );
  }

  return (
    <BrowserRouter>
      <Routes>
        <Route path="/" element={<DashboardPage />} />

        {/* If user is already logged in, don't let them open the login page again */}
        <Route 
          path="/login" 
          element={token ? <Navigate to="/records" replace /> : <LoginPage />} 
        />

        {/* 🔐 All Protected Management Routes */}
        <Route
          path="/records"
          element={
            <ProtectedRoute>
              <RecordsPage />
            </ProtectedRoute>
          }
        />
        
        <Route
          path="/records/:id"
          element={
            <ProtectedRoute>
              <RecordDetailsPage />
            </ProtectedRoute>
          }
        />

        <Route
          path="/add-record"
          element={
            <ProtectedRoute>
              <AddRecordPage />
            </ProtectedRoute>
          }
        />
        
        {/* Catch-all fallback */}
        <Route path="*" element={<Navigate to="/" replace />} />
      </Routes>
    </BrowserRouter>
  );
};

export default AppRoutes;