import MainLayout from "../layouts/MainLayout";
import PageHeader from "../components/common/PageHeader";
import { useRecords } from "../context/RecordsContext";

const DashboardPage = () => {
  const { records = [] } = useRecords();

  const totalRecords = records?.length || 0;
  const approvedRecords = (records || []).filter((r) => r?.status === "APPROVED").length;
  const returnedRecords = (records || []).filter((r) => r?.status === "RETURNED").length;
  const pendingRecords = (records || []).filter((r) => r?.status === "PENDING").length;

  const cards = [
    { title: "Total Filings", value: totalRecords, gradient: "from-slate-50 to-slate-100 border-slate-200" },
    { title: "Accepted", value: approvedRecords, gradient: "from-emerald-50 to-emerald-100 border-emerald-200" },
    { title: "Returned", value: returnedRecords, gradient: "from-rose-50 to-rose-100 border-rose-200" },
    { title: "Pending", value: pendingRecords, gradient: "from-amber-50 to-amber-100 border-amber-200" },
  ];

  // Restored original secure date processor safely guarding against crashes
  const formatFilingDate = (dateStr) => {
    if (!dateStr) return "N/A";

    const recordDate = new Date(dateStr);
    const today = new Date();

    const isToday = 
      recordDate.getDate() === today.getDate() &&
      recordDate.getMonth() === today.getMonth() &&
      recordDate.getFullYear() === today.getFullYear();

    if (isToday) {
      return (
        <span className="text-emerald-600 font-bold bg-emerald-50 px-2 py-1 rounded-md text-xs uppercase tracking-wider">
          Today
        </span>
      );
    }

    const day = String(recordDate.getDate()).padStart(2, '0');
    const month = String(recordDate.getMonth() + 1).padStart(2, '0'); 
    const year = recordDate.getFullYear();
    
    return <span className="text-slate-600 font-medium">{`${day}-${month}-${year}`}</span>;
  };

  // Helper utility function to parse 24h standard backend strings to clean 12h AM/PM text
  const formatTimeToAMPM = (timeStr) => {
    if (!timeStr) return "N/A";
    const [hours, minutes] = timeStr.split(":");
    let hh = parseInt(hours, 10);
    const ampm = hh >= 12 ? "PM" : "AM";
    hh = hh % 12 || 12; 
    return `${hh}:${minutes} ${ampm}`;
  };

  return (
    <MainLayout>
      <PageHeader title="Dashboard" subtitle="Court E-Filing Registry Overview" />

      <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mt-8">
        {cards.map((card) => (
          <div
            key={card.title}
            className={`bg-gradient-to-br ${card.gradient} rounded-2xl border shadow-sm p-6 transition-transform hover:scale-[1.02]`}
          >
            <p className="text-slate-600 text-xs font-bold uppercase tracking-widest">
              {card.title}
            </p>
            <h2 className="text-4xl font-extrabold text-[#1f3a56] mt-3">
              {card.value}
            </h2>
          </div>
        ))}
      </div>

      {/* Recent Activity Table */}
      <div className="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 mt-8 overflow-hidden">
        <h2 className="text-xl font-bold text-[#1f3a56] mb-6 flex items-center">
          Recent Case Log
        </h2>

        <div className="overflow-x-auto">
          <table className="w-full text-left table-auto">
            <thead>
              <tr className="text-slate-400 text-[10px] uppercase tracking-widest border-b border-slate-100">
                {/* FIXED: Renamed column title header */}
                <th className="px-4 py-3">Case No / Year</th>
                <th className="px-4 py-3">Filing Date</th>
                <th className="px-4 py-3">Filing Time</th>
                <th className="px-4 py-3">Description</th>
                <th className="px-4 py-3 text-right">Status</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-50">
              {records?.slice(0, 5).map((record, index) => (
                <tr key={record?.id || index} className="hover:bg-slate-50 transition-colors">
                  
                  {/* FIXED: Wiped out caseInfoNo and displayed combined Case Number / Year layout */}
                  <td className="px-4 py-4 text-sm">
                    <div className="font-bold text-[#1f3a56]">
                      {record?.caseNo || "—"} / {record?.caseYear || "—"}
                    </div>
                    {record?.caseTypeCode && (
                      <div className="text-[11px] text-gray-400 font-bold uppercase tracking-wide mt-0.5">
                        {record?.caseNature || "Civil"} • <span className="text-[#1f3a56] bg-slate-100 px-1.5 py-0.5 rounded text-[10px]">{record?.caseTypeCode}</span>
                      </div>
                    )}
                  </td>

                  {/* Smart Filing Date Column (Today vs DD-MM-YYYY) */}
                  <td className="px-4 py-4 text-sm font-medium">
                    {formatFilingDate(record?.filingDate)}
                  </td>

                  {/* Filing Time Column */}
                  <td className="px-4 py-4 text-slate-600 text-sm font-medium">
                    {formatTimeToAMPM(record?.filingTime)}
                  </td>

                  {/* Description / Remark */}
                  <td className="px-4 py-4 text-slate-500 italic text-sm max-w-xs truncate">
                    {record?.latestRemark || "Registry update processed."}
                  </td>

                  {/* Status Badges */}
                  <td className="px-4 py-4 text-right">
                    <span className={`px-3 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider ${
                      record?.status === "APPROVED" ? "bg-emerald-100 text-emerald-700" :
                      record?.status === "RETURNED" ? "bg-rose-100 text-rose-700" : 
                      "bg-amber-100 text-amber-700"
                    }`}>
                      {record?.status || "PENDING"}
                    </span>
                  </td>

                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </MainLayout>
  );
};

export default DashboardPage;