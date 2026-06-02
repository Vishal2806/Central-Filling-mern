import { useMemo, useState } from "react";

import records from "../data/records";

import MainLayout from "../layouts/MainLayout";

import PageHeader from "../components/PageHeader";
import SearchBar from "../components/SearchBar";
import Table from "../components/Table";

const DataPage = () => {
  const [search, setSearch] = useState("");
  const [filter, setFilter] = useState("all");

  const filteredData = useMemo(() => {
    return records.filter((item) => {
      const matchesSearch =
        item.caseNo
          .toLowerCase()
          .includes(search.toLowerCase()) ||
        item.advocate
          .toLowerCase()
          .includes(search.toLowerCase());

      const matchesFilter =
        filter === "all"
          ? true
          : item.remark
              .toLowerCase()
              .includes(filter.toLowerCase());

      return matchesSearch && matchesFilter;
    });
  }, [search, filter]);

  return (
    <MainLayout>
      <PageHeader
        title="E-Filing Registration Records"
        subtitle="Central Filing Counter Management System"
      />

      <SearchBar
        search={search}
        setSearch={setSearch}
        filter={filter}
        setFilter={setFilter}
      />

      <Table data={filteredData} />
    </MainLayout>
  );
};

export default DataPage;