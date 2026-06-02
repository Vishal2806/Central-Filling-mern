const PageHeader = ({ title, subtitle }) => {
  return (
    <div className="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
      <h1 className="text-3xl font-bold text-[#1f3a56]">
        {title}
      </h1>

      <p className="text-gray-500 mt-2">
        {subtitle}
      </p>
    </div>
  );
};

export default PageHeader;