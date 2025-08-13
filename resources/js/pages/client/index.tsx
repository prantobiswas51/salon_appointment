
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
            <div className='p-2 m-4'>
                <h1 className="text-2xl font-bold mb-4">Clients</h1>

                <form action="" method="get" className='mb-4 flex'>
                    <input
                        type="text"
                        placeholder='Search by name, phone, email'
                        className='border p-2 px-4 min-w-lg rounded-md'
                    />
                    <button
                        type="submit"
                        className='border flex rounded-md ml-2 p-2 bg-pink-600 text-white'
                    >
                        <Search /> <span className='ml-1'>Search</span>
                    </button>
                </form>

                <table className="w-full border-gray-300 rounded-lg shadow-md text-sm text-left">
                    <thead className="text-gray-900 uppercase rounded-md bg-pink-300">
                        <tr>
                            <th className="px-6 py-3 border-b p-2">Id</th>
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
                                <td className="px-6 py-4 border-b flex hover:cursor-pointer items-center">
                                    <FilePen
                                        className='text-amber-500 hover:text-amber-600'
                                        onClick={() => openModal(client)}
                                    />
                                    <Trash className='text-red-500 hover:text-red-600 ml-3' />
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>

                {isModalOpen && editingClient && (
                    <div className="fixed inset-0  bg-opacity-50 bg-gray-700/20 flex justify-center items-center">
                        <div className="bg-sky-100 min-w-xl p-6 rounded-md shadow-lg w-96">
                            <h2 className="text-xl font-bold mb-4">Edit Client</h2>
                            <form onSubmit={handleUpdateClient}>
                                <div className="mb-4">
                                    <label className="block mb-2">Name</label>
                                    <input
                                        type="text"
                                        className="border p-2 rounded-md w-full"
                                        value={editingClient.name}
                                        onChange={(e) =>
                                            setEditingClient({
                                                ...editingClient,
                                                name: e.target.value,
                                            })
                                        }
                                    />
                                </div>
                                <div className="mb-4">
                                    <label className="block mb-2">Email</label>
                                    <input
                                        type="text"
                                        className="border p-2 rounded-md w-full"
                                        value={editingClient.email || ''}
                                        onChange={(e) =>
                                            setEditingClient({
                                                ...editingClient,
                                                email: e.target.value,
                                            })
                                        }
                                    />
                                </div>
                                <div className="mb-4">
                                    <label className="block mb-2">Date of Birth</label>
                                    <input
                                        type="date"
                                        className="border p-2 rounded-md w-full"
                                        value={editingClient.dob || ''}
                                        onChange={(e) =>
                                            setEditingClient({
                                                ...editingClient,
                                                dob: e.target.value,
                                            })
                                        }
                                    />
                                </div>

                                <div className="mb-4">
                                    <label className="block mb-2">Phone</label>
                                    <input
                                        type="text"
                                        className="border p-2 rounded-md w-full"
                                        value={editingClient.phone}
                                        onChange={(e) =>
                                            setEditingClient({
                                                ...editingClient,
                                                phone: e.target.value,
                                            })
                                        }
                                    />
                                </div>
                                <div className="mb-4">
                                    <label className="block mb-2">Status</label>
                                    <select
                                        className="border p-2 rounded-md w-full"
                                        value={editingClient.status || ''}
                                        onChange={(e) =>
                                            setEditingClient({
                                                ...editingClient,
                                                status: e.target.value,
                                            })
                                        }
                                    >
                                        <option value="Red">Red</option>
                                        <option value="Green">Green</option>
                                        <option value="Yellow">Yellow</option>
                                    </select>
                                </div>

                                <div className="mb-4">
                                    <label className="block mb-2">Notes</label>
                                    <textarea
                                        className="border p-2 rounded-md w-full"
                                        value={editingClient.email || ''}
                                        onChange={(e) =>
                                            setEditingClient({
                                                ...editingClient,
                                                email: e.target.value,
                                            })
                                        }
                                    />

                                </div>
                                <div className="flex justify-end">
                                    <button
                                        type="button"
                                        className="mr-2 border px-4 py-2 rounded-md text-gray-500"
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
