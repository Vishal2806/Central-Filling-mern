import { useNavigate } from "react-router-dom";

import { useAuth } from "../../context/AuthContext";

const Topbar = () => {
  const navigate = useNavigate();

  const { user, logout } =
    useAuth();

  // Logout
  const handleLogout = () => {
    logout();

    navigate("/login");
  };

  return (
    <header className="h-[80px] bg-white border-b border-gray-200 shadow-sm flex items-center justify-between px-8">
      {/* Left */}
      <div>
        <h2 className="text-2xl font-bold text-[#1f3a56]">
          Court E-Filing System
        </h2>

        <p className="text-sm text-gray-500">
          Central Filing Counter
        </p>
      </div>

      {/* Right */}
      <div className="flex items-center gap-5">
        {/* User Info */}
        <div className="flex items-center gap-3">
          <div className="h-11 w-11 rounded-full bg-[#1f3a56] text-white flex items-center justify-center font-bold uppercase">
            {user?.name?.charAt(0)}
          </div>

          <div>
            <p className="font-semibold text-sm">
              {user?.name}
            </p>

            <p className="text-xs text-gray-500">
              {user?.role}
            </p>
          </div>
        </div>

        {/* Logout */}
        <button
          onClick={handleLogout}
          className="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition"
        >
          Logout
        </button>
      </div>
    </header>
  );
};

export default Topbar;