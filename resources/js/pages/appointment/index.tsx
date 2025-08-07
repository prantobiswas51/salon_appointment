import React from 'react';
import { Head, router, usePage } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { FilePen, Trash } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Appointment',
        href: '/appointment',
    },
];

type Client = {
    id: number;
    name: string;
    phone: string;
    email?: string;
};

type Appointment = {
    id: number;
    client_id: number;
    service: string;
    duration: string;
    attendence_status: string;
    appointment_time: string;
    status: string;
    notes: string;
    client: Client;
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
    const { appointments } = usePage<{ appointments: Paginated<Appointment> }>().props;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Appointment" />
            <div className='p-2 m-4'>
                <h1 className="text-2xl font-bold mb-4">Scheduled Appointments</h1>

                <form action="" method="get" className='mb-4'>
                    <input
                        type="text"
                        placeholder='Search by name, phone, email'
                        className='border p-2 rounded-md'
                    />
                    <button
                        type="submit"
                        className='border rounded-md ml-2 p-2 px-4 bg-pink-600 text-white'
                    >
                        Search
                    </button>
                </form>

                <table className="w-full border-gray-300 rounded-lg shadow-sm text-sm text-left">
                    <thead className="text-gray-900 uppercase bg-pink-300">
                        <tr>
                            <th className="px-6 py-3 border-b">Id</th>
                            <th className="px-6 py-3 border-b">Name</th>
                            <th className="px-6 py-3 border-b">Phone</th>
                            <th className="px-6 py-3 border-b">Service</th>
                            <th className="px-6 py-3 border-b">Time</th>
                            <th className="px-6 py-3 border-b">Status</th>
                            <th className="px-6 py-3 border-b">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {appointments.data.map((appointment) => (
                            <tr key={appointment.id} className="hover:bg-gray-50 transition">
                                <td className="px-6 py-4 border-b">{appointment.id}</td>
                                <td className="px-6 py-4 border-b">{appointment.client?.name}</td>
                                <td className="px-6 py-4 border-b">{appointment.client?.phone}</td>
                                <td className="px-6 py-4 border-b">{appointment.service}</td>
                                <td className="px-6 py-4 border-b">{appointment.appointment_time}</td>
                                <td className="px-6 py-4 border-b">{appointment.status}</td>
                                <td className="px-6 py-4 border-b flex hover:cursor-pointer items-center"> 
                                    <FilePen className='text-amber-500 hover:text-amber-600 '/>
                                    <Trash className='text-red-500 hover:text-red-600 ml-3'/> 
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>

                {/* ✅ Pagination Controls (must be outside tbody) */}
                <div className="flex justify-center mt-6 space-x-2">
                    {appointments.links.map((link, index) => (
                        <button
                            key={index}
                            disabled={!link.url}
                            dangerouslySetInnerHTML={{ __html: link.label }}
                            onClick={() => link.url && router.visit(link.url)}
                            className={`px-3 py-1 border rounded hover:cursor-pointer
                                ${link.active ? 'bg-pink-600 text-white' : 'bg-white text-pink-600'}
                                ${!link.url ? 'opacity-50 cursor-not-allowed' : ''}`}
                        />
                    ))}
                </div>
            </div>

        </AppLayout>
    );
}
