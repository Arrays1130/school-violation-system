import React, { useState, useEffect } from "react";
import { Head, Link, useForm } from "@inertiajs/react";
import { Eye, EyeOff, Lock, Mail, Shield, AlertCircle, CheckCircle, Loader2 } from "lucide-react";

export default function EnhancedLogin({ status, canResetPassword }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        email: "",
        password: "",
        remember: false,
    });

    const [showPassword, setShowPassword] = useState(false);
    const [loginAttempts, setLoginAttempts] = useState(0);
    const [showCaptcha, setShowCaptcha] = useState(false);
    const [captchaValue, setCaptchaValue] = useState("");
    const [generatedCaptcha, setGeneratedCaptcha] = useState("");
    const [emailError, setEmailError] = useState("");
    const [passwordError, setPasswordError] = useState("");
    const [isLoading, setIsLoading] = useState(false);

    // Generate CAPTCHA when needed
    useEffect(() => {
        if (loginAttempts >= 3) {
            setShowCaptcha(true);
            generateCaptcha();
        }
    }, [loginAttempts]);

    const generateCaptcha = () => {
        const chars = "ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789";
        let captcha = "";
        for (let i = 0; i < 6; i++) {
            captcha += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        setGeneratedCaptcha(captcha);
        setCaptchaValue("");
    };

    // Real-time email validation
    const validateEmail = (email) => {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!email) return "Email is required";
        if (!emailRegex.test(email)) return "Please enter a valid email address";
        if (!email.endsWith("@ilink.edu.ph") && !email.endsWith("@admin.ilink.edu.ph")) {
            return "Please use your institutional email (@ilink.edu.ph)";
        }
        return "";
    };

    // Real-time password validation
    const validatePassword = (password) => {
        if (!password) return "Password is required";
        if (password.length < 8) return "Password must be at least 8 characters";
        return "";
    };

    const handleEmailChange = (e) => {
        const value = e.target.value;
        setData('email', value);
        setEmailError(validateEmail(value));
    };

    const handlePasswordChange = (e) => {
        const value = e.target.value;
        setData('password', value);
        setPasswordError(validatePassword(value));
    };

    const submit = async (e) => {
        e.preventDefault();
        
        // Validate before submission
        const emailValidation = validateEmail(data.email);
        const passwordValidation = validatePassword(data.password);
        
        if (emailValidation || passwordValidation) {
            setEmailError(emailValidation);
            setPasswordError(passwordValidation);
            return;
        }

        // Validate CAPTCHA if shown
        if (showCaptcha && captchaValue !== generatedCaptcha) {
            setEmailError("CAPTCHA verification failed. Please try again.");
            generateCaptcha();
            return;
        }

        setIsLoading(true);
        
        try {
            await post(route("login"), {
                onFinish: () => {
                    reset("password");
                    setIsLoading(false);
                    setLoginAttempts(prev => prev + 1);
                },
                onError: (errors) => {
                    setIsLoading(false);
                    setLoginAttempts(prev => prev + 1);
                    
                    // Generate new CAPTCHA on error
                    if (loginAttempts >= 2) {
                        setShowCaptcha(true);
                        generateCaptcha();
                    }
                },
            });
        } catch (error) {
            setIsLoading(false);
            setLoginAttempts(prev => prev + 1);
        }
    };

    const isFormValid = () => {
        return data.email && data.password && 
               !validateEmail(data.email) && 
               !validatePassword(data.password) &&
               (!showCaptcha || captchaValue === generatedCaptcha);
    };

    return (
        <div className="min-h-screen bg-gradient-to-br from-slate-50 to-indigo-50 flex flex-col items-center justify-center p-4 font-sans">
            <Head>
                <title>Secure Login — I-Link CST Disciplinary System</title>
                <meta name="description" content="Secure login portal for I-Link College disciplinary system administrators" />
            </Head>

            {/* Skip to main content for accessibility */}
            <a href="#login-form" className="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50 focus:px-4 focus:py-2 focus:bg-white focus:text-indigo-600 focus:font-bold focus:rounded-lg focus:shadow-lg">
                Skip to login form
            </a>

            <div className="w-full max-w-md">
                {/* Security Header */}
                <div className="mb-8 text-center">
                    <div className="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-600 mb-4 shadow-lg">
                        <Shield className="w-8 h-8 text-white" />
                    </div>
                    <h1 className="text-2xl font-bold text-slate-900 mb-2">Secure Access Portal</h1>
                    <p className="text-slate-600 text-sm">I-Link College Disciplinary System</p>
                    
                    {/* Security Badge */}
                    <div className="inline-flex items-center gap-2 mt-3 px-3 py-1.5 bg-emerald-50 border border-emerald-200 rounded-full">
                        <div className="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                        <span className="text-xs font-semibold text-emerald-700">🔒 SSL Secured Connection</span>
                    </div>
                </div>

                {/* Login Card */}
                <div className="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
                    {/* Card Header */}
                    <div className="px-6 py-5 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-indigo-50">
                        <h2 className="text-lg font-semibold text-slate-900 flex items-center gap-2">
                            <Lock className="w-5 h-5 text-indigo-600" />
                            Administrator Login
                        </h2>
                        <p className="text-sm text-slate-500 mt-1">Enter your credentials to access the system</p>
                    </div>

                    {/* Card Body */}
                    <div className="p-6">
                        {status && (
                            <div className="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-xl flex items-start gap-3" role="alert">
                                <CheckCircle className="w-5 h-5 text-emerald-600 flex-shrink-0 mt-0.5" />
                                <div>
                                    <p className="text-sm font-medium text-emerald-800">{status}</p>
                                </div>
                            </div>
                        )}

                        {/* Login Attempts Warning */}
                        {loginAttempts > 0 && (
                            <div className="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-xl" role="alert">
                                <div className="flex items-center gap-2 mb-2">
                                    <AlertCircle className="w-5 h-5 text-amber-600" />
                                    <span className="text-sm font-semibold text-amber-800">Security Notice</span>
                                </div>
                                <p className="text-sm text-amber-700">
                                    {loginAttempts === 1 ? "1 unsuccessful login attempt" : `${loginAttempts} unsuccessful login attempts`}
                                </p>
                                {loginAttempts >= 3 && (
                                    <p className="text-xs text-amber-600 mt-2">
                                        Additional security verification required
                                    </p>
                                )}
                            </div>
                        )}

                        <form onSubmit={submit} id="login-form" className="space-y-5" noValidate>
                            {/* Email Field */}
                            <div className="space-y-2">
                                <label htmlFor="email" className="block text-sm font-semibold text-slate-700">
                                    Institutional Email
                                    <span className="text-rose-500 ml-1" aria-hidden="true">*</span>
                                    <span className="sr-only"> (required)</span>
                                </label>
                                <div className="relative group">
                                    <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <Mail className={`w-5 h-5 ${data.email ? 'text-indigo-600' : 'text-slate-400'} transition-colors`} />
                                    </div>
                                    <input
                                        id="email"
                                        type="email"
                                        name="email"
                                        value={data.email}
                                        className={`w-full pl-10 pr-4 py-3 bg-white border rounded-lg text-slate-900 placeholder-slate-400 outline-none transition-all text-sm
                                            ${errors.email || emailError ? 'border-rose-300 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20' : 
                                              data.email && !emailError ? 'border-emerald-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20' :
                                              'border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20'}`}
                                        placeholder="name@ilink.edu.ph"
                                        autoComplete="username"
                                        required
                                        aria-required="true"
                                        aria-invalid={!!errors.email || !!emailError}
                                        aria-describedby={errors.email || emailError ? "email-error" : "email-help"}
                                        onChange={handleEmailChange}
                                        onBlur={() => setEmailError(validateEmail(data.email))}
                                    />
                                </div>
                                {(errors.email || emailError) && (
                                    <p id="email-error" className="text-sm text-rose-600 flex items-center gap-1">
                                        <AlertCircle className="w-4 h-4" />
                                        {errors.email || emailError}
                                    </p>
                                )}
                                <p id="email-help" className="text-xs text-slate-500">
                                    Use your institutional email ending with @ilink.edu.ph
                                </p>
                            </div>

                            {/* Password Field */}
                            <div className="space-y-2">
                                <div className="flex items-center justify-between">
                                    <label htmlFor="password" className="block text-sm font-semibold text-slate-700">
                                        Password
                                        <span className="text-rose-500 ml-1" aria-hidden="true">*</span>
                                        <span className="sr-only"> (required)</span>
                                    </label>
                                    {canResetPassword && (
                                        <Link 
                                            href={route('password.request')}
                                            className="text-sm text-indigo-600 hover:text-indigo-700 font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 rounded"
                                        >
                                            Forgot password?
                                        </Link>
                                    )}
                                </div>
                                <div className="relative group">
                                    <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <Lock className={`w-5 h-5 ${data.password ? 'text-indigo-600' : 'text-slate-400'} transition-colors`} />
                                    </div>
                                    <input
                                        id="password"
                                        type={showPassword ? "text" : "password"}
                                        name="password"
                                        value={data.password}
                                        className={`w-full pl-10 pr-12 py-3 bg-white border rounded-lg text-slate-900 placeholder-slate-400 outline-none transition-all text-sm
                                            ${errors.password || passwordError ? 'border-rose-300 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20' : 
                                              data.password && !passwordError ? 'border-emerald-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20' :
                                              'border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20'}`}
                                        placeholder="Enter your password"
                                        autoComplete="current-password"
                                        required
                                        aria-required="true"
                                        aria-invalid={!!errors.password || !!passwordError}
                                        aria-describedby={errors.password || passwordError ? "password-error" : undefined}
                                        onChange={handlePasswordChange}
                                        onBlur={() => setPasswordError(validatePassword(data.password))}
                                    />
                                    <button
                                        type="button"
                                        className="absolute inset-y-0 right-0 pr-3 flex items-center"
                                        onClick={() => setShowPassword(!showPassword)}
                                        aria-label={showPassword ? "Hide password" : "Show password"}
                                        aria-pressed={showPassword}
                                    >
                                        {showPassword ? (
                                            <EyeOff className="w-5 h-5 text-slate-400 hover:text-slate-600 transition-colors" />
                                        ) : (
                                            <Eye className="w-5 h-5 text-slate-400 hover:text-slate-600 transition-colors" />
                                        )}
                                    </button>
                                </div>
                                {(errors.password || passwordError) && (
                                    <p id="password-error" className="text-sm text-rose-600 flex items-center gap-1">
                                        <AlertCircle className="w-4 h-4" />
                                        {errors.password || passwordError}
                                    </p>
                                )}
                                <div className="flex items-center justify-between text-xs text-slate-500">
                                    <span>Must be at least 8 characters</span>
                                    <span className={`font-medium ${data.password.length >= 8 ? 'text-emerald-600' : 'text-slate-400'}`}>
                                        {data.password.length}/8
                                    </span>
                                </div>
                            </div>

                            {/* CAPTCHA Section */}
                            {showCaptcha && (
                                <div className="space-y-3 p-4 bg-slate-50 border border-slate-200 rounded-xl">
                                    <div className="flex items-center justify-between">
                                        <span className="text-sm font-semibold text-slate-700">Security Verification</span>
                                        <button
                                            type="button"
                                            onClick={generateCaptcha}
                                            className="text-xs text-indigo-600 hover:text-indigo-700 font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 rounded"
                                        >
                                            Refresh CAPTCHA
                                        </button>
                                    </div>
                                    <div className="flex items-center gap-4">
                                        <div className="flex-1 bg-white border border-slate-300 rounded-lg p-3 font-mono text-lg tracking-wider text-center select-none">
                                            {generatedCaptcha}
                                        </div>
                                        <input
                                            type="text"
                                            value={captchaValue}
                                            onChange={(e) => setCaptchaValue(e.target.value.toUpperCase())}
                                            className="flex-1 px-3 py-2 border border-slate-300 rounded-lg text-sm uppercase"
                                            placeholder="Enter CAPTCHA"
                                            aria-label="Enter the CAPTCHA text shown above"
                                            maxLength={6}
                                        />
                                    </div>
                                    <p className="text-xs text-slate-500">
                                        Type the characters shown above (case-sensitive)
                                    </p>
                                </div>
                            )}

                            {/* Remember Me */}
                            <div className="flex items-center">
                                <input
                                    id="remember"
                                    type="checkbox"
                                    name="remember"
                                    checked={data.remember}
                                    onChange={(e) => setData('remember', e.target.checked)}
                                    className="w-4 h-4 rounded bg-white border-slate-300 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-0 transition-all"
                                />
                                <label htmlFor="remember" className="ml-2 text-sm text-slate-600 cursor-pointer">
                                    Keep me signed in on this device
                                </label>
                            </div>

                            {/* Submit Button */}
                            <button
                                type="submit"
                                disabled={processing || isLoading || !isFormValid()}
                                className="w-full py-3.5 px-4 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-70 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                                aria-busy={processing || isLoading}
                            >
                                {processing || isLoading ? (
                                    <>
                                        <Loader2 className="w-5 h-5 animate-spin" />
                                        <span>Authenticating...</span>
                                    </>
                                ) : (
                                    <>
                                        <Lock className="w-5 h-5" />
                                        <span>Sign In to Dashboard</span>
                                    </>
                                )}
                            </button>
                        </form>

                        {/* Security Footer */}
                        <div className="mt-8 pt-6 border-t border-slate-200">
                            <div className="space-y-3">
                                <div className="flex items-center justify-center gap-4 text-xs text-slate-500">
                                    <span className="flex items-center gap-1">
                                        <div className="w-2 h-2 rounded-full bg-emerald-500"></div>
                                        HTTPS Secure
                                    </span>
                                    <span className="flex items-center gap-1">
                                        <div className="w-2 h-2 rounded-full bg-blue-500"></div>
                                        Encrypted Connection
                                    </span>
                                </div>
                                <p className="text-xs text-slate-500 text-center">
                                    For security assistance, contact:{" "}
                                    <a href="mailto:it-support@ilink.edu.ph" className="text-indigo-600 hover:text-indigo-700 font-medium">
                                        it-support@ilink.edu.ph
                                    </a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Footer */}
                <div className="mt-8 text-center">
                    <p className="text-sm text-slate-600">
                        &copy; {new Date().getFullYear()} I-Link College of Science and Technology
                    </p>
                    <p className="text-xs text-slate-500 mt-2">
                        Disciplinary System v2.0 • Last updated: June 2026
                    </p>
                </div>
            </div>

            {/* Accessibility Announcements */}
            <div className="sr-only" aria-live="polite" aria-atomic="true">
                {processing || isLoading ? "Authenticating, please wait" : "Login form ready"}
            </div>
        </div>
    );
}