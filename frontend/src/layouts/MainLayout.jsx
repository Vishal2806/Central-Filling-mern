import Sidebar from "../components/layout/Sidebar";
import Topbar from "../components/layout/Topbar";

const MainLayout = ({ children }) => {
  return (
    <div className="flex bg-[#eef2f7] min-h-screen">
      <Sidebar />

      <div className="flex-1 flex flex-col">
        <Topbar />

        <main className="p-8">
          {children}
        </main>
      </div>
    </div>
  );
};

export default MainLayout;