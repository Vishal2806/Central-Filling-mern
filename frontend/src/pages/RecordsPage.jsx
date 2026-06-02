import { useMemo, useState } from "react";
import MainLayout from "../layouts/MainLayout";
import PageHeader from "../components/common/PageHeader";
import SearchFilters from "../components/records/SearchFilters";
import RecordsTable from "../components/records/RecordsTable";
import { useRecords } from "../context/RecordsContext";

const RecordsPage = () => {
  const { records, loading } = useRecords();
  const [search, setSearch] = useState("");
  const [status, setStatus] = useState("ALL");

  const filteredRecords = useMemo(() => {
    if (loading) return []; 

    return records.filter((record) => {
      // Clean Search Tracker: Matches strictly by Case No or Advocate Name now
      const matchesSearch =
        (record.caseNo?.toLowerCase() || "").includes(search.toLowerCase()) ||
        (record.advocateName?.toLowerCase() || "").includes(search.toLowerCase());

      const matchesStatus =
        status === "ALL" ? true : record.status === status;

      return matchesSearch && matchesStatus;
    });
  }, [records, search, status, loading]); 

  if (loading) {
    return (
      <MainLayout>
        <div style={{ padding: "40px", textAlign: "center" }} className="text-xl font-semibold">
          Loading records database...
        </div>
      </MainLayout>
    );
  }

  return (
    <MainLayout>
      <PageHeader
        title="Records Management"
        subtitle="Search and manage filing records"
      />

      <SearchFilters
        search={search}
        setSearch={setSearch}
        status={status}
        setStatus={setStatus}
      />

      {/* Passing clean filtered records data grid downwards */}
      <RecordsTable records={filteredRecords} />
    </MainLayout>
  );
};

export default RecordsPage;