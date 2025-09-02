import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';

export default function Welcome() {
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
    <h1 className="text-2xl font-semibold">Terms & Conditions</h1>

    <p>
      These Terms and Conditions govern your use of our platform. By accessing the platform, you agree to these terms in full. This website is available only to salon owners and authorized staff members in Italy.
    </p>

    <h2 className="text-lg font-medium">1. Access Restriction</h2>
    <p>
      Only registered salon owners and their staff may access this platform. Unauthorized access is strictly prohibited and may be prosecuted under applicable law.
    </p>

    <h2 className="text-lg font-medium">2. Account Responsibility</h2>
    <p>
      You are responsible for maintaining the confidentiality of your login credentials. You agree to notify us immediately if you suspect unauthorized use of your account.
    </p>

    <h2 className="text-lg font-medium">3. Use of Platform</h2>
    <p>
      You agree to use the platform only for legitimate salon-related purposes. You must not misuse the platform, transmit harmful content, or engage in illegal activity.
    </p>

    <h2 className="text-lg font-medium">4. Use of WhatsApp API</h2>
    <p>
      The platform integrates WhatsApp Business API solely for customer communication by salons. Misuse of the API, including spam or unsolicited messages, may result in suspension or termination of access.
    </p>

    <h2 className="text-lg font-medium">5. Intellectual Property</h2>
    <p>
      All content, branding, and design elements of this platform are the intellectual property of Karbon Tech Store. Unauthorized reproduction is prohibited.
    </p>

    <h2 className="text-lg font-medium">6. Liability</h2>
    <p>
      We are not liable for any direct or indirect damages resulting from the use of the platform, except where required by Italian law.
    </p>

    <h2 className="text-lg font-medium">7. Termination</h2>
    <p>
      We reserve the right to suspend or terminate any account that violates these terms, without prior notice.
    </p>

    <h2 className="text-lg font-medium">8. Governing Law</h2>
    <p>
      These Terms are governed by the laws of Italy and the European Union. Any disputes will be subject to the exclusive jurisdiction of the courts in Italy.
    </p>

    <h2 className="text-lg font-medium">9. Contact</h2>
    <p>
      For any questions or concerns about these Terms, contact: <strong>salonipro@gmail.com</strong>.
    </p>
  </div>
</div>

                <div className="hidden h-14.5 lg:block"></div>
            </div>
        </>
    );
}
