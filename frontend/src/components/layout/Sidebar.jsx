import { Link, useLocation } from "react-router-dom";

const Sidebar = () => {
  const location = useLocation();

  // Clean menu structure - keeping only your core administrative navigation views
  const menus = [
    {
      name: "Dashboard",
      path: "/",
    },
    {
      name: "Records",
      path: "/records",
    },
    {
      name: "Add Record",
      path: "/add-record",
    },
  ];

  return (
    <aside className="w-[260px] bg-[#1f3a56] min-h-screen text-white shadow-xl">
      {/* Logo Wrapper */}
      <div className="h-[80px] border-b border-[#38536d] flex items-center px-6">
        <div>
          <h1 className="text-2xl font-bold">E-Filing</h1>
          <p className="text-xs text-gray-300 mt-1">Registry Management</p>
        </div>
      </div>

      {/* Navigation Stack Link Items */}
      <div className="p-4 flex flex-col gap-2">
        {menus.map((menu) => (
          <Link
            key={menu.path}
            to={menu.path}
            className={`px-4 py-3 rounded-lg transition duration-200 ${
              location.pathname === menu.path
                ? "bg-[#d4af37] text-black font-semibold"
                : "hover:bg-[#163047] text-gray-200"
            }`}
          >
            {menu.name}
          </Link>
        ))}
      </div>
    </aside>
  );
};

export default Sidebar;