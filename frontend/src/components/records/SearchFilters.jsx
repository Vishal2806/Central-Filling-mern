const SearchFilters = ({
  search,
  setSearch,
  status,
  setStatus,
}) => {
  return (
    <div className="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 mt-6">
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-5">
        {/* Search */}
        <div>
          <label className="block mb-2 text-sm font-semibold text-gray-600">
            Search Records
          </label>

          <input
            type="text"
            placeholder="Search by Filing No, Case No or Advocate"
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            className="w-full border border-gray-300 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-[#1f3a56]"
          />
        </div>

        {/* Filter */}
        <div>
          <label className="block mb-2 text-sm font-semibold text-gray-600">
            Filter by Status
          </label>

          <select
            value={status}
            onChange={(e) => setStatus(e.target.value)}
            className="w-full border border-gray-300 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-[#1f3a56]"
          >
            <option value="ALL">All Status</option>

            <option value="APPROVED">
              Approved
            </option>

            <option value="RETURNED">
              Returned
            </option>

            <option value="PENDING">
              Pending
            </option>

            <option value="RESUBMITTED">
              Resubmitted
            </option>
          </select>
        </div>
      </div>
    </div>
  );
};

export default SearchFilters;