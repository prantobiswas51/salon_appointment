import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { Timer, CalendarDays } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

export default function Dashboard() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex flex-col md:grid md:grid-cols-2">
                <div className="w-auto border p-2 bg-sky-200/30 border-sky-500 m-2 rounded-md shadow-sm">
                    <h2 className='text-xl font-bold flex'><Timer className='mr-2' />Appointment in Progress</h2>
                    <h3 className='font-semibold flex'>Pranto Biswas</h3>
                    <h3>Hair Cut and Shaving</h3>
                    <div className="flex justify-between items-center"><h3>Time : 2.40 PM</h3> <p className='bg-green-200 px-2 p-1 rounded-2xl text-sm'>In progress</p></div>
                </div>
                <div className="w-auto border p-2 bg-amber-200/30 border-amber-500 m-2 rounded-md shadow-sm">
                    <h2 className='text-xl font-bold flex'><Timer className='mr-2' />Next Appointment</h2>
                    <h3 className='font-semibold flex'>Pranto Biswas</h3>
                    <h3>Hair Cut and Shaving</h3>
                    <div className="flex justify-between items-center"><h3>Time : 2.40 PM</h3> <p className=' px-2 p-1 rounded-2xl text-sm'>Next</p></div>
                </div>
            </div>

            <h2 className='text-xl font-bold flex mx-2 mt-6'>Today's Agenda</h2>
            <div className="w-auto mx-2 rounded-md md:grid md:grid-cols-3 gap-3">

                <div className="p-2 my-2 rounded-md bg-pink-300/60 border border-pink-500 shadow-sm">
                    <div className="font-bold">Today</div>
                    <p className='text-3xl flex'>6 <div className="text-sm ml-2">appointments</div></p>
                </div>
                <div className="p-2 my-2 rounded-md bg-pink-300/60 border border-pink-500 shadow-sm">
                    <div className="font-bold">Week</div>
                    <p className='text-3xl flex'>15 <div className="text-sm ml-2">appointments</div></p>
                </div>
                <div className="p-2 my-2 rounded-md bg-pink-300/60 border border-pink-500 shadow-sm">
                    <div className="font-bold">Month</div>
                    <p className='text-3xl flex'>45 <div className="text-sm ml-2">appointments</div></p>
                </div>
            </div>


            {/* Todays Agenda */}
            <h2 className='text-xl font-bold mx-2 mt-6 flex'><CalendarDays className='mr-2' />Today's Agenda</h2>
            <div className="w-auto mx-2 rounded-md">

                <div className="p-2 my-2 rounded-md border-green-600 border shadow-sm md:flex lg:max-w-[50%] md:justify-between md:items-center">
                    <div className="flex justify-between ">
                        <div className="font-bold text-gray-600 flex  items-center"><Timer />11.45 AM </div>
                        <span className='bg-green-600 p-2 py-1 rounded-2xl text-white md:ml-4'>Completed</span>
                    </div>
                    <div className="font-bold ml-2">Pranto Biswas</div>
                    <div className=" ml-2">Hair Cutting and Shaving</div>
                </div>

                <div className="p-2 my-2 rounded-md border-green-600 border shadow-sm md:flex lg:max-w-[50%] md:justify-between md:items-center">
                    <div className="flex justify-between ">
                        <div className="font-bold text-gray-600 flex  items-center"><Timer />11.45 AM </div>
                        <span className='bg-green-600 p-2 py-1 rounded-2xl text-white md:ml-4'>Completed</span>
                    </div>
                    <div className="font-bold ml-2">Pranto Biswas</div>
                    <div className=" ml-2">Hair Cutting and Shaving</div>
                </div>

                <div className="p-2 my-2 rounded-md border-green-600 border shadow-sm md:flex lg:max-w-[50%] md:justify-between md:items-center">
                    <div className="flex justify-between">
                        <div className="font-bold text-gray-600 flex  items-center"><Timer />11.45 AM </div>
                        <span className='bg-blue-400/60 text-black p-2 py-1 rounded-2xl md:ml-4'>In Progress</span>
                    </div>
                    <div className="font-bold ml-2">Pranto Biswas</div>
                    <div className=" ml-2">Hair Cutting and Shaving</div>
                </div>

                <div className="p-2 my-2 rounded-md border-green-600 border shadow-sm md:flex lg:max-w-[50%] md:justify-between md:items-center">
                    <div className="flex justify-between">
                        <div className="font-bold text-gray-600 flex  items-center"><Timer />11.45 AM </div>
                        <span className='bg-pink-400/60 text-black p-2 py-1 rounded-2xl md:ml-4'>Incoming</span>
                    </div>
                    <div className="font-bold ml-2">Pranto Biswas</div>
                    <div className=" ml-2">Hair Cutting and Shaving</div>
                </div>

            </div>
        </AppLayout>
    );
}
