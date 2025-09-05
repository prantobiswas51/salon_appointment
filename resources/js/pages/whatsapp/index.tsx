import React, { useState } from "react";
import { Head, router, usePage } from "@inertiajs/react";
import AppLayout from "@/layouts/app-layout";
import { type BreadcrumbItem } from "@/types";
import { FilePen, Search } from "lucide-react";

const breadcrumbs: BreadcrumbItem[] = [
  { title: "Whatsapp API", href: "/Whatsapp" },
];

type Whatsapp = {
  id: number;
  token: string;
  number_id: string;
};

type PageProps = {
  whatsapps: Whatsapp[];
};

export default function Index() {
  const { whatsapps } = usePage<PageProps>().props;
  const [selected, setSelected] = useState<Whatsapp | null>(null);
  const [showModal, setShowModal] = useState(false);
  const [form, setForm] = useState({ token: "", number_id: "" });

  const openModal = (whatsapp: Whatsapp) => {
    setSelected(whatsapp);
    setForm({ token: whatsapp.token, number_id: whatsapp.number_id });
    setShowModal(true);
  };

  const handleUpdate = () => {
    if (!selected) return;
    router.put(
      route("whatsapp.update", selected.id),
      form,
      {
        onSuccess: () => setShowModal(false),
      }
    );
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Whatsapp API" />
      <div className="p-2 m-4">
        <h1 className="text-2xl font-bold mb-4">Setup Whatsapp API</h1>

        {/* Table of Records */}
        <table className="min-w-full bg-white rounded shadow">
          <thead>
            <tr className="bg-gray-100 text-left">
              <th className="p-2">ID</th>
              <th className="p-2">Token</th>
              <th className="p-2">Number ID</th>
              <th className="p-2">Action</th>
            </tr>
          </thead>
          <tbody>
            {whatsapps.map((w) => (
              <tr key={w.id} className="border-b">
                <td className="p-2">{w.id}</td>
                <td className="p-2 truncate max-w-xs">{w.token}</td>
                <td className="p-2">{w.number_id}</td>
                <td className="p-2">
                  <button
                    onClick={() => openModal(w)}
                    className="p-2 bg-blue-500 text-white rounded hover:bg-blue-600 flex items-center gap-1"
                  >
                    <FilePen size={16} /> Edit
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>

        {/* Edit Modal */}
        {showModal && (
          <div className="fixed inset-0 flex items-center justify-center bg-gray-200/50 bg-opacity-50">
            <div className="bg-white p-6 rounded shadow w-96">
              <h2 className="text-xl font-bold mb-4">Edit Whatsapp API</h2>

              <div className="mb-4">
                <label className="block text-sm font-medium">Token</label>
                <textarea
                  value={form.token}
                  onChange={(e) => setForm({ ...form, token: e.target.value })}
                  className="w-full border p-2 rounded"
                ></textarea>
              </div>

              <div className="mb-4">
                <label className="block text-sm font-medium">Number ID</label>
                <input
                  type="text"
                  value={form.number_id}
                  onChange={(e) =>
                    setForm({ ...form, number_id: e.target.value })
                  }
                  className="w-full border p-2 rounded"
                />
              </div>

              <div className="flex justify-end gap-2">
                <button
                  onClick={() => setShowModal(false)}
                  className="px-4 py-2 bg-gray-300 rounded"
                >
                  Cancel
                </button>
                <button
                  onClick={handleUpdate}
                  className="px-4 py-2 bg-blue-500 text-white rounded"
                >
                  Save
                </button>
              </div>
            </div>
          </div>
        )}
      </div>
    </AppLayout>
  );
}
