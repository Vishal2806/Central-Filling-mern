import React from "react";
import { Link } from "react-router-dom";
import { FileText, ShieldCheck, Search } from "lucide-react";

const HomePage = () => {
  return (
    <div className="min-h-screen bg-[#f4f1e8]">
      {/* Header */}
      <header className="bg-[#1f3a56] text-white border-b-4 border-[#d4af37] shadow-lg">
        <div className="max-w-7xl mx-auto px-6 py-5">
          <h1 className="text-3xl font-bold uppercase tracking-wide">
            E-Filing Registration Portal
          </h1>

          <p className="mt-2 text-sm tracking-wide text-gray-200">
            Central Filing Counter Management System
          </p>
        </div>
      </header>

      {/* Hero */}
      <section className="max-w-7xl mx-auto px-6 py-14">
        <div className="bg-white shadow-xl border border-gray-300 rounded-md p-10">
          <h2 className="text-4xl font-bold text-[#1f3a56]">
            Digital Court Filing Record System
          </h2>

          <p className="mt-5 text-gray-700 leading-7 text-lg">
            This portal helps maintain and manage filing records,
            paper book submissions, advocate details, and tracking
            of returned documents digitally.
          </p>

          <div className="mt-8 flex gap-4 flex-wrap">
            <Link
              to="/records"
              className="bg-[#1f3a56] hover:bg-[#163047] text-white px-6 py-3 rounded-md font-semibold shadow-md transition"
            >
              Open Records
            </Link>

            <Link
              to="/login"
              className="border-2 border-[#1f3a56] text-[#1f3a56] hover:bg-[#1f3a56] hover:text-white px-6 py-3 rounded-md font-semibold transition"
            >
              Login
            </Link>
          </div>
        </div>
      </section>

      {/* Features */}
      <section className="max-w-7xl mx-auto px-6 pb-16">
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div className="bg-white border border-gray-300 rounded-md shadow-md p-6">
            <FileText className="text-[#1f3a56]" size={40} />

            <h3 className="mt-4 text-xl font-bold text-[#1f3a56]">
              Record Management
            </h3>

            <p className="mt-3 text-gray-600 leading-6">
              Store and manage all filing records digitally with
              structured entries.
            </p>
          </div>

          <div className="bg-white border border-gray-300 rounded-md shadow-md p-6">
            <Search className="text-[#1f3a56]" size={40} />

            <h3 className="mt-4 text-xl font-bold text-[#1f3a56]">
              Search & Filter
            </h3>

            <p className="mt-3 text-gray-600 leading-6">
              Easily search by case number, advocate name, or filing
              status.
            </p>
          </div>

          <div className="bg-white border border-gray-300 rounded-md shadow-md p-6">
            <ShieldCheck className="text-[#1f3a56]" size={40} />

            <h3 className="mt-4 text-xl font-bold text-[#1f3a56]">
              Secure System
            </h3>

            <p className="mt-3 text-gray-600 leading-6">
              Maintain secure access and proper document tracking
              workflow.
            </p>
          </div>
        </div>
      </section>

      {/* Footer */}
      <footer className="bg-[#1f3a56] text-center text-white py-4">
        Government E-Filing Registration Portal © 2026
      </footer>
    </div>
  );
};

export default HomePage;