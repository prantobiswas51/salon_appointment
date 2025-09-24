
import React, { useState } from 'react';
import { Head, usePage } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { FilePen, Search, Trash } from 'lucide-react';
import { type BreadcrumbItem } from '@/types';
import { router } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Clients',
        href: '/clients',
    },
];

type Client = {
    id: number;
    name: string;
    phone: string;
    email?: string;
    dob?: string;
    notes?: string;
    status?: string;
};

type Paginated<T> = {
    data: T[];
    links: {
        url: string | null;
        label: string;
        active: boolean;
    }[];
};

export default function Index() {
    const { clients } = usePage<{ clients: Paginated<Client> }>().props;

    const [isModalOpen, setModalOpen] = useState(false);
    const [editingClient, setEditingClient] = useState<Client | null>(null);

    const openModal = (client: Client) => {
        setEditingClient(client);
        setModalOpen(true);
    };

    const closeModal = () => {
        setModalOpen(false);
        setEditingClient(null);
    };

    const handleUpdateClient = (e: React.FormEvent) => {
        e.preventDefault();

        if (editingClient) {
            // Use Inertia.put() for a PUT request
            router.put(route('client.update', editingClient.id), {
                ...editingClient,
            });

            closeModal(); // Close modal after updating
        }
    };


    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Clients" />
            <div className="p-2 m-4">
                <h1 className="text-2xl font-bold mb-4">Clients</h1>

                {/* Search form */}
                <form action="" method="get" className="mb-4 flex flex-col sm:flex-row gap-2">
                    <input
                        type="text"
                        placeholder="Search by name, phone, email"
                        className="border p-2 px-4 w-full sm:min-w-[300px] rounded-md"
                    />
                    <button
                        type="submit"
                        className="border flex items-center justify-center rounded-md p-2 bg-pink-600 text-white"
                    >
                        <Search className="w-4 h-4" /> <span className="ml-1">Search</span>
                    </button>
                </form>

                {/* ✅ Desktop table view */}
                <div className="hidden lg:block overflow-x-auto">
                    <table className="w-full border-gray-300 rounded-lg shadow-md text-sm text-left">
                        <thead className="text-gray-900 uppercase bg-pink-300">
                            <tr>
                                <th className="px-6 py-3 border-b">Id</th>
                                <th className="px-6 py-3 border-b">Name</th>
                                <th className="px-6 py-3 border-b">Phone</th>
                                <th className="px-6 py-3 border-b">Status</th>
                                <th className="px-6 py-3 border-b">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {clients.data.map((client) => (
                                <tr key={client.id} className="hover:bg-gray-50 transition">
                                    <td className="px-6 py-4 border-b">{client.id}</td>
                                    <td className="px-6 py-4 border-b">{client.name}</td>
                                    <td className="px-6 py-4 border-b">{client.phone}</td>
                                    <td className="px-6 py-4 border-b">{client.status}</td>
                                    <td className="px-6 py-4 border-b flex items-center space-x-3">
                                        <FilePen
                                            className="text-amber-500 hover:text-amber-600 cursor-pointer"
                                            onClick={() => openModal(client)}
                                        />
                                        <Trash className="text-red-500 hover:text-red-600 cursor-pointer" />
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>

                {/* ✅ Mobile card view */}
                <div className="block lg:hidden space-y-4">
                    {clients.data.map((client) => (
                        <div
                            key={client.id}
                            className="bg-white dark:bg-gray-900  shadow-md rounded-md p-4 border space-y-2"
                        >
                            <div className="flex justify-between">
                                <span className="font-bold text-gray-500">{client.name}</span>
                                <span className="text-sm text-gray-500">ID: {client.id}</span>
                            </div>
                            <p className="text-gray-500 text-sm">📞 {client.phone}</p>
                            <p className="text-gray-500 text-sm">Status: {client.status}</p>
                            <div className="flex space-x-3 pt-2">
                                <FilePen
                                    className="text-amber-500 hover:text-amber-600 cursor-pointer"
                                    onClick={() => openModal(client)}
                                />
                                <Trash className="text-red-500 hover:text-red-600 cursor-pointer" />
                            </div>
                        </div>
                    ))}
                </div>

                {/* Modal (same for all views) */}
                {isModalOpen && editingClient && (
                    <div className="fixed inset-0 bg-gray-700/40 flex justify-center items-center p-4 z-50">
                        <div className="bg-sky-100 dark:bg-sky-950 w-full max-w-md p-6 rounded-md shadow-lg">
                            <h2 className="text-xl font-bold mb-4">Edit Client</h2>
                            <form onSubmit={handleUpdateClient} className="space-y-4">
                                {/* Name */}
                                <div>
                                    <label className="block mb-2">Name</label>
                                    <input
                                        type="text" required
                                        className="border p-2 rounded-md w-full dark:border-gray-100"
                                        value={editingClient.name}
                                        onChange={(e) =>
                                            setEditingClient({ ...editingClient, name: e.target.value })
                                        }
                                    />
                                </div>

                                {/* Email */}
                                <div>
                                    <label className="block mb-2">Email</label>
                                    <input
                                        type="text"
                                        className="border p-2 rounded-md w-full dark:border-gray-100"
                                        value={editingClient.email || ""}
                                        onChange={(e) =>
                                            setEditingClient({ ...editingClient, email: e.target.value })
                                        }
                                    />
                                </div>

                                {/* DOB */}
                                <div>
                                    <label className="block mb-2">Date of Birth</label>
                                    <input
                                        type="date"
                                        className="border p-2 rounded-md w-full dark:border-gray-100"
                                        value={editingClient.dob || ""}
                                        onChange={(e) =>
                                            setEditingClient({ ...editingClient, dob: e.target.value })
                                        }
                                    />
                                </div>

                                {/* Phone */}
                                <div>
                                    <label className="block mb-2">Phone</label>
                                    <input
                                        type="text" required
                                        className="border p-2 rounded-md w-full dark:border-gray-100"
                                        value={editingClient.phone}
                                        onChange={(e) =>
                                            setEditingClient({ ...editingClient, phone: e.target.value })
                                        }
                                    />
                                </div>

                                {/* Status */}
                                <div>
                                    <label className="block mb-2">Status</label>
                                    <select
                                        className="border p-2 rounded-md w-full dark:border-gray-100"
                                        value={editingClient.status || ""} 
                                        onChange={(e) =>
                                            setEditingClient({ ...editingClient, status: e.target.value })
                                        }
                                    >
                                        <option value="Red">Red</option>
                                        <option value="Green">Green</option>
                                        <option value="Yellow">Yellow</option>
                                    </select>
                                </div>

                                {/* Notes */}
                                <div>
                                    <label className="block mb-2">Notes</label>
                                    <textarea
                                        className="border p-2 rounded-md w-full dark:border-gray-100"
                                        value={editingClient.notes || ""}
                                        onChange={(e) =>
                                            setEditingClient({ ...editingClient, notes: e.target.value })
                                        }
                                    />
                                </div>

                                {/* Buttons */}
                                <div className="flex justify-end space-x-2">
                                    <button
                                        type="button"
                                        className="border px-4 py-2 rounded-md text-gray-500"
                                        onClick={closeModal}
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        type="submit"
                                        className="bg-blue-600 text-white px-4 py-2 rounded-md"
                                    >
                                        Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                )}
            </div>
        </AppLayout>

    );
}
