import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';

export default function Policy() {
    const { auth } = usePage<SharedData>().props;

    return (
        <>
            <Head title="Welcome">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>
            <div className="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] lg:justify-center lg:p-8 dark:bg-[#0a0a0a]">
                <header className="mb-6 w-full max-w-[335px] text-sm not-has-[nav]:hidden lg:max-w-4xl">
                    <nav className="flex items-center justify-end gap-4">
                        {auth.user ? (
                            <Link
                                href={route('dashboard')}
                                className="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                            >
                                Dashboard
                            </Link>
                        ) : (
                            <>
                                <Link
                                    href={route('privacy_policy')}
                                    className="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"
                                >
                                    Privacy Policy
                                </Link>
                                <Link
                                    href={route('terms_condition')}
                                    className="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"
                                >
                                    Terms & Conditions
                                </Link>
                                <Link
                                    href={route('login')}
                                    className="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"
                                >
                                    Log in
                                </Link>
                                <Link
                                    href={route('register')}
                                    className="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                                >
                                    Register
                                </Link>
                            </>
                        )}
                    </nav>
                </header>

                <div className="flex w-full items-center justify-center opacity-100 transition-opacity duration-750 lg:grow starting:opacity-0">
  <div className="max-w-3xl text-sm leading-relaxed space-y-4">
    <h1 className="text-2xl font-semibold">Privacy Policy</h1>

    <p>
      This Privacy Policy outlines how we collect, use, and protect your personal information on our platform, which is accessible only to salon owners and staff members. This website is operated in compliance with the General Data Protection Regulation (GDPR) applicable in Italy and the European Union.
    </p>

    <h2 className="text-lg font-medium">1. Data Controller</h2>
    <p>
      The data controller for this website is Karbon Tech Store. You can contact us at: <strong>salonipro@gmail.com</strong>.
    </p>

    <h2 className="text-lg font-medium">2. Information We Collect</h2>
    <ul className="list-disc list-inside space-y-1">
      <li>Full name</li>
      <li>Email address</li>
      <li>Phone number</li>
      <li>Salon name and business information</li>
      <li>Login credentials</li>
      <li>WhatsApp number (if used for customer communications)</li>
      <li>Usage logs (IP address, browser, device info)</li>
    </ul>

    <h2 className="text-lg font-medium">3. Purpose of Data Processing</h2>
    <p>We process your data to:</p>
    <ul className="list-disc list-inside space-y-1">
      <li>Authenticate and authorize salon owners and staff</li>
      <li>Provide access to platform features and communication tools</li>
      <li>Enable WhatsApp Business API communication for salon services</li>
      <li>Ensure platform security and prevent unauthorized access</li>
      <li>Comply with legal obligations under Italian and EU law</li>
    </ul>

    <h2 className="text-lg font-medium">4. Legal Basis</h2>
    <p>
      We process your personal data based on your explicit consent and for the performance of a contract (providing salon services).
    </p>

    <h2 className="text-lg font-medium">5. Data Sharing</h2>
    <p>
      Your personal data is not shared with third parties unless required by law or necessary to provide services (e.g., WhatsApp for messaging).
    </p>

    <h2 className="text-lg font-medium">6. Data Retention</h2>
    <p>
      We retain your personal data for as long as your account remains active. You may request deletion at any time by contacting us.
    </p>

    <h2 className="text-lg font-medium">7. Your Rights</h2>
    <p>Under GDPR, you have the right to:</p>
    <ul className="list-disc list-inside space-y-1">
      <li>Access your data</li>
      <li>Correct or update your data</li>
      <li>Request data deletion</li>
      <li>Withdraw your consent</li>
      <li>File a complaint with the <em>Garante per la protezione dei dati personali</em> (Italian Data Protection Authority)</li>
    </ul>

    <h2 className="text-lg font-medium">8. Contact</h2>
    <p>
      For privacy-related inquiries, contact us at: <strong>salonipro@gmail.com</strong>.
    </p>
  </div>
</div>

                <div className="hidden h-14.5 lg:block"></div>
            </div>
        </>
    );
}
