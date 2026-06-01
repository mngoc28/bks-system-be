// @ts-nocheck
import React, { useState } from "react";

// Mock Data & SVGs for crisp, dependency-free rendering
const CheckIcon = () => (
  <svg className="w-5 h-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={3}>
    <path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7" />
  </svg>
);

const QrIcon = () => (
  <svg className="w-40 h-40 text-slate-800" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1}>
    <path strokeLinecap="round" strokeLinejoin="round" d="M12 4v1m0 11v1m0-6V8m0-4H8m8 0h-4m4 0h4m-4 12h4m-4-6h4M4 8h4m-4 4h4m-4 4h4M4 4h16v16H4V4z" />
  </svg>
);

const AlertIcon = () => (
  <svg className="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
    <path strokeLinecap="round" strokeLinejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
  </svg>
);

const CopyIcon = () => (
  <svg className="w-4 h-4 text-slate-400 hover:text-slate-600 cursor-pointer" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
    <path strokeLinecap="round" strokeLinejoin="round" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
  </svg>
);

const UserIcon = () => (
  <svg className="w-4 h-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
    <path strokeLinecap="round" strokeLinejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
  </svg>
);

const HomeIcon = () => (
  <svg className="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
    <path strokeLinecap="round" strokeLinejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
  </svg>
);

const CalendarIcon = () => (
  <svg className="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
    <path strokeLinecap="round" strokeLinejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
  </svg>
);

const UploadIcon = () => (
  <svg className="w-8 h-8 text-sky-500 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
    <path strokeLinecap="round" strokeLinejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
  </svg>
);

export default function BookingDepositUI() {
  const [activeTab, setActiveTab] = useState("success");
  const [copied, setCopied] = useState<string | null>(null);

  // Screen 3 Simulator States
  const [isDepositPaid, setIsDepositPaid] = useState(false);
  const [isContractSigned, setIsContractSigned] = useState(false);
  const [showTooltip, setShowTooltip] = useState(false);

  // Screen 4 Mock State
  const [deposits, setDeposits] = useState([
    { id: 1, guest: "Nguyễn Văn An", room: "Room 402 - Master Suite", amount: 1500000, date: "2026-06-01", status: "payment_submitted", receipt: "https://images.unsplash.com/photo-1554415707-6e8cfc93fe23?auto=format&fit=crop&w=400&q=50" },
    { id: 2, guest: "Trần Thị Bình", room: "Room 105 - Studio", amount: 800000, date: "2026-06-01", status: "confirmed_by_partner", receipt: "" }
  ]);

  const handleApproveDeposit = (id: number) => {
    setDeposits(prev => prev.map(d => d.id === id ? { ...d, status: "confirmed_by_partner" } : d));
  };

  const triggerCopy = (text: string, label: string) => {
    setCopied(label);
    navigator.clipboard?.writeText?.(text);
    setTimeout(() => setCopied(null), 2000);
  };

  return (
    <div className="w-full min-h-[600px] bg-slate-900 text-slate-100 p-6 flex flex-col font-sans select-none">
      {/* Title & Specs Header */}
      <div className="border-b border-slate-800 pb-4 mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <span className="text-xs font-black uppercase tracking-wider text-sky-400">UI Design Wireframe v1</span>
          <h1 className="text-xl font-black text-white">Đặt Cọc Linh Hoạt & Nghiệp Vụ Tài Chính</h1>
        </div>
        <div className="flex gap-2">
          <span className="px-3 py-1 rounded-full text-xs font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">
            Trạng thái: PENDING_UI_APPROVAL
          </span>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-4 gap-6 flex-1">
        {/* Navigation Tabs */}
        <div className="flex flex-col gap-2 bg-slate-950/40 p-3 rounded-2xl border border-slate-800 h-fit">
          <span className="text-[10px] font-black uppercase tracking-widest text-slate-500 px-3 py-1">Danh sách màn hình</span>
          
          <button
            onClick={() => setActiveTab("success")}
            className={`w-full text-left px-4 py-3 rounded-xl text-xs font-bold transition-all flex items-center justify-between ${activeTab === "success" ? "bg-sky-600 text-white shadow-lg shadow-sky-600/10" : "text-slate-400 hover:bg-slate-800/50 hover:text-white"}`}
          >
            <span>1. Đặt phòng thành công</span>
            <span className="text-[10px] px-1.5 py-0.5 rounded bg-black/20 text-sky-200">Success</span>
          </button>

          <button
            onClick={() => setActiveTab("stay")}
            className={`w-full text-left px-4 py-3 rounded-xl text-xs font-bold transition-all flex items-center justify-between ${activeTab === "stay" ? "bg-sky-600 text-white shadow-lg shadow-sky-600/10" : "text-slate-400 hover:bg-slate-800/50 hover:text-white"}`}
          >
            <span>2. Stay Portal - Chi tiết lưu trú</span>
            <span className="text-[10px] px-1.5 py-0.5 rounded bg-black/20 text-sky-200">Guest</span>
          </button>

          <button
            onClick={() => setActiveTab("bookings")}
            className={`w-full text-left px-4 py-3 rounded-xl text-xs font-bold transition-all flex items-center justify-between ${activeTab === "bookings" ? "bg-sky-600 text-white shadow-lg shadow-sky-600/10" : "text-slate-400 hover:bg-slate-800/50 hover:text-white"}`}
          >
            <span>3. Quản lý Đặt phòng (SOP)</span>
            <span className="text-[10px] px-1.5 py-0.5 rounded bg-black/20 text-sky-200">Host</span>
          </button>

          <button
            onClick={() => setActiveTab("finance")}
            className={`w-full text-left px-4 py-3 rounded-xl text-xs font-bold transition-all flex items-center justify-between ${activeTab === "finance" ? "bg-sky-600 text-white shadow-lg shadow-sky-600/10" : "text-slate-400 hover:bg-slate-800/50 hover:text-white"}`}
          >
            <span>4. Quản lý cọc tài chính</span>
            <span className="text-[10px] px-1.5 py-0.5 rounded bg-black/20 text-sky-200">Finance</span>
          </button>
        </div>

        {/* Content View Area */}
        <div className="lg:col-span-3 bg-slate-950 p-6 rounded-3xl border border-slate-850 shadow-inner flex flex-col justify-between min-h-[500px]">
          
          {/* TAB 1: Booking Success Screen */}
          {activeTab === "success" && (
            <div className="space-y-6">
              <div className="border-b border-slate-800 pb-3 flex justify-between items-center">
                <span className="text-xs text-sky-400 font-bold">Màn hình /booking-success</span>
                <span className="text-slate-500 text-[10px] italic">Thanh toán cọc trực tiếp + Đếm ngược</span>
              </div>

              {/* simulated Booking Success Card */}
              <div className="max-w-xl mx-auto bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-6">
                <div className="text-center space-y-2">
                  <div className="mx-auto size-12 rounded-full bg-emerald-500/10 flex items-center justify-center">
                    <CheckIcon />
                  </div>
                  <h2 className="text-lg font-black text-white">Đặt phòng thành công!</h2>
                  <p className="text-xs text-slate-400">Mã đặt phòng: <span className="font-mono font-bold text-slate-200 bg-slate-950 px-2 py-1 rounded">BKS-682910</span></p>
                </div>

                {/* Simulated VietQR Deposit Block */}
                <div className="bg-slate-950 border border-indigo-900/50 rounded-2xl p-4 space-y-4">
                  <div className="flex items-center gap-2 text-xs font-bold text-indigo-400">
                    <AlertIcon />
                    <span>Yêu cầu thanh toán đặt cọc giữ phòng</span>
                  </div>

                  {/* simulated Countdown */}
                  <div className="bg-slate-900/50 p-2.5 rounded-xl border border-slate-800">
                    <div className="flex justify-between text-[10px] font-bold text-slate-400 mb-1">
                      <span>Thời gian giữ phòng chờ cọc còn lại:</span>
                      <span className="text-amber-400 font-mono">01:59:42</span>
                    </div>
                    <div className="w-full bg-slate-800 h-1.5 rounded-full overflow-hidden">
                      <div className="bg-gradient-to-r from-amber-500 to-rose-500 h-full w-[85%] rounded-full animate-pulse" />
                    </div>
                  </div>

                  <div className="flex flex-col md:flex-row gap-4 items-center justify-center py-2">
                    {/* Simulated QR */}
                    <div className="bg-white p-3 rounded-2xl flex items-center justify-center shadow-lg border border-slate-200">
                      <QrIcon />
                    </div>

                    <div className="flex-1 w-full text-xs space-y-2">
                      <div className="flex justify-between border-b border-slate-900 py-1.5">
                        <span className="text-slate-400">Số tiền cọc:</span>
                        <span className="font-bold text-white">1,500,000 VND</span>
                      </div>
                      <div className="flex justify-between border-b border-slate-900 py-1.5 items-center">
                        <span className="text-slate-400">Số tài khoản:</span>
                        <span className="font-mono font-bold text-slate-200 flex items-center gap-1.5">
                          1234567890 
                          <span onClick={() => triggerCopy("1234567890", "stk")}>
                            <CopyIcon />
                          </span>
                        </span>
                      </div>
                      <div className="flex justify-between border-b border-slate-900 py-1.5 items-center">
                        <span className="text-slate-400">Nội dung CK:</span>
                        <span className="font-mono font-bold text-amber-400 flex items-center gap-1.5 uppercase">
                          BKSSETTLE10 
                          <span onClick={() => triggerCopy("BKSSETTLE10", "noidung")}>
                            <CopyIcon />
                          </span>
                        </span>
                      </div>
                      {copied && <p className="text-[10px] text-emerald-400 text-right font-bold">Đã sao chép {copied === "stk" ? "Số tài khoản" : "Nội dung chuyển khoản"}!</p>}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          )}

          {/* TAB 2: Stay Portal Detail Screen */}
          {activeTab === "stay" && (
            <div className="space-y-6">
              <div className="border-b border-slate-800 pb-3 flex justify-between items-center">
                <span className="text-xs text-sky-400 font-bold">Màn hình /bks-stay/bookings/:id</span>
                <span className="text-slate-500 text-[10px] italic">Thanh tiến trình + Upload biên lai cọc + Stay Voucher PNG</span>
              </div>

              {/* Progress Steps bar */}
              <div className="flex justify-between items-center bg-slate-900 p-4 rounded-2xl border border-slate-800 text-[10px] font-bold overflow-x-auto gap-2">
                <div className="flex items-center gap-1 text-slate-500"><CheckIcon /> <span>Đặt thành công</span></div>
                <div className="w-4 h-px bg-slate-800 shrink-0" />
                <div className="flex items-center gap-1 text-slate-500"><CheckIcon /> <span>Ký hợp đồng</span></div>
                <div className="w-4 h-px bg-slate-800 shrink-0" />
                <div className="flex items-center gap-1 text-indigo-400"><div className="size-2 rounded-full bg-indigo-500 animate-ping mr-1" /> <span>Thanh toán cọc</span></div>
                <div className="w-4 h-px bg-slate-800 shrink-0" />
                <div className="text-slate-600">Sẵn sàng Check-in</div>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                {/* Upload proof block */}
                <div className="bg-slate-900 border border-slate-800 rounded-3xl p-5 space-y-4">
                  <span className="text-xs font-bold text-white block">Tải ảnh biên lai chuyển khoản</span>
                  <p className="text-[11px] text-slate-400 leading-relaxed">
                    Nếu bạn chuyển khoản trực tiếp ngoài hệ thống cho Host, hãy upload ảnh chụp giao dịch thành công để Host duyệt cọc nhanh.
                  </p>
                  
                  <div className="border-2 border-dashed border-slate-800 rounded-2xl p-6 flex flex-col items-center justify-center bg-slate-950/50 hover:border-sky-500/50 hover:bg-slate-900/20 transition-all cursor-pointer">
                    <UploadIcon />
                    <span className="text-xs text-slate-300 font-semibold">Nhấp hoặc kéo thả ảnh biên lai vào đây</span>
                    <span className="text-[9px] text-slate-500 mt-1">Định dạng hỗ trợ: JPG, PNG (tối đa 5MB)</span>
                  </div>
                </div>

                {/* Stay Voucher block */}
                <div className="bg-slate-900 border border-slate-800 rounded-3xl p-5 space-y-4">
                  <span className="text-xs font-bold text-white block">Phiếu Xác Nhận Lưu Trú</span>
                  <div className="bg-slate-950 rounded-2xl p-3 border border-slate-800 space-y-2 text-[10px]">
                    <div className="flex justify-between font-bold border-b border-slate-900 pb-1">
                      <span className="text-slate-400">KHÁCH HÀNG:</span>
                      <span className="text-slate-200">NGUYỄN VĂN AN</span>
                    </div>
                    <div className="flex justify-between font-bold border-b border-slate-900 pb-1">
                      <span className="text-slate-400">HẠNG PHÒNG:</span>
                      <span className="text-slate-200">DELUXE MASTER SUITE</span>
                    </div>
                    <div className="flex justify-between font-bold">
                      <span className="text-slate-400">MÃ ĐĂNG KÝ:</span>
                      <span className="text-emerald-400">BKS-682910</span>
                    </div>
                  </div>

                  <div className="flex gap-2">
                    <button className="flex-1 py-2 px-3 bg-sky-600 text-white rounded-xl text-xs font-bold hover:bg-sky-700 active:scale-95 transition-all">
                      Tải Voucher (PNG)
                    </button>
                    <button className="flex-1 py-2 px-3 border border-slate-800 hover:bg-slate-800 text-slate-300 rounded-xl text-xs font-bold active:scale-95 transition-all">
                      In phiếu (Ctrl+P)
                    </button>
                  </div>
                </div>
              </div>
            </div>
          )}

          {/* TAB 3: Partner Bookings SOP Screen */}
          {activeTab === "bookings" && (
            <div className="space-y-6">
              <div className="border-b border-slate-800 pb-3 flex justify-between items-center">
                <span className="text-xs text-sky-400 font-bold">Màn hình /partner/bookings - Bảng Lễ Tân (SOP Panel)</span>
                <span className="text-slate-500 text-[10px] italic">Chốt chặn Check-in cứng (Frontend Gate)</span>
              </div>

              {/* Simulation Controls for checking Gate Lock */}
              <div className="bg-slate-900 border border-indigo-950/80 rounded-2xl p-4 flex flex-wrap gap-4 items-center justify-between">
                <div className="text-xs">
                  <span className="font-bold text-indigo-400 block mb-1">Cài đặt mô phỏng chốt chặn check-in (Frontend Testing):</span>
                  <p className="text-[10px] text-slate-400">Thao tác mô phỏng việc khách nộp cọc và ký hợp đồng để xem nút Check-in thay đổi.</p>
                </div>
                <div className="flex gap-4">
                  <label className="flex items-center gap-2 text-xs cursor-pointer">
                    <input
                      type="checkbox"
                      checked={isDepositPaid}
                      onChange={(e) => setIsDepositPaid(e.target.checked)}
                      className="rounded accent-sky-500"
                    />
                    <span>1. Đã đóng cọc</span>
                  </label>
                  <label className="flex items-center gap-2 text-xs cursor-pointer">
                    <input
                      type="checkbox"
                      checked={isContractSigned}
                      onChange={(e) => setIsContractSigned(e.target.checked)}
                      className="rounded accent-sky-500"
                    />
                    <span>2. Đã ký hợp đồng</span>
                  </label>
                </div>
              </div>

              {/* Simulated Booking Row */}
              <div className="bg-slate-900 border border-slate-800 rounded-3xl p-4 space-y-4">
                <div className="flex justify-between items-start border-b border-slate-800 pb-3">
                  <div className="space-y-1">
                    <span className="inline-flex items-center gap-1.5 text-xs font-bold text-white">
                      <UserIcon /> NGUYỄN VĂN AN
                    </span>
                    <span className="flex items-center gap-1 text-[11px] text-slate-400">
                      <HomeIcon /> Room 402 - Master Suite
                    </span>
                  </div>
                  <div className="text-right text-xs">
                    <span className="text-[10px] text-slate-500 block">THỜI GIAN LƯU TRÚ</span>
                    <span className="font-bold text-slate-300">01/06/2026 - 03/06/2026 (2 đêm)</span>
                  </div>
                </div>

                <div className="flex justify-between items-center">
                  <div className="flex gap-3 text-[10px] font-bold">
                    <span className={`px-2.5 py-0.5 rounded-full border ${isDepositPaid ? "bg-emerald-500/10 text-emerald-400 border-emerald-500/20" : "bg-rose-500/10 text-rose-400 border-rose-500/20"}`}>
                      Cọc: {isDepositPaid ? "Đã ký quỹ" : "Chưa thanh toán"}
                    </span>
                    <span className={`px-2.5 py-0.5 rounded-full border ${isContractSigned ? "bg-emerald-500/10 text-emerald-400 border-emerald-500/20" : "bg-rose-500/10 text-rose-400 border-rose-500/20"}`}>
                      Hợp đồng: {isContractSigned ? "Đã ký" : "Chờ ký"}
                    </span>
                  </div>

                  {/* CHECK-IN GATE BUTTON */}
                  <div className="relative">
                    <button
                      onMouseEnter={() => {
                        if (!isDepositPaid || !isContractSigned) setShowTooltip(true);
                      }}
                      onMouseLeave={() => setShowTooltip(false)}
                      onClick={() => {
                        if (isDepositPaid && isContractSigned) {
                          alert("Check-in thành công!");
                        }
                      }}
                      disabled={!isDepositPaid || !isContractSigned}
                      className={`h-9 px-4 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 ${isDepositPaid && isContractSigned ? "bg-sky-600 hover:bg-sky-700 text-white cursor-pointer active:scale-95 shadow-md shadow-sky-600/10" : "bg-slate-800 text-slate-500 border border-slate-700 cursor-not-allowed"}`}
                    >
                      <span>Check-in</span>
                    </button>

                    {/* Tooltip cảnh báo */}
                    {showTooltip && (
                      <div className="absolute right-0 bottom-12 w-64 bg-rose-950 border border-rose-500/30 text-rose-200 text-[10px] font-medium p-3 rounded-xl shadow-2xl z-50 animate-bounce">
                        <p className="font-bold flex items-center gap-1 text-rose-400 mb-1">
                          <AlertIcon /> Chặn Check-in!
                        </p>
                        <p>Bạn phải hoàn tất các thủ tục sau trước khi khách nhận phòng:</p>
                        <ul className="list-disc pl-3 mt-1 space-y-0.5">
                          {!isContractSigned && <li>Hợp đồng thuê nhà chưa ký.</li>}
                          {!isDepositPaid && <li>Tiền cọc chưa được đóng/duyệt.</li>}
                        </ul>
                      </div>
                    )}
                  </div>
                </div>
              </div>
            </div>
          )}

          {/* TAB 4: Partner Finance Escrow Screen */}
          {activeTab === "finance" && (
            <div className="space-y-6">
              <div className="border-b border-slate-800 pb-3 flex justify-between items-center">
                <span className="text-xs text-sky-400 font-bold">Màn hình /partner/finance - Tab Ký quỹ & Cọc khách hàng</span>
                <span className="text-slate-500 text-[10px] italic">Duyệt biên lai cọc + Nghiệm thu hư hao tài sản</span>
              </div>

              {/* KPI cards for Escrow */}
              <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div className="bg-slate-900 border border-slate-800 p-4 rounded-2xl space-y-1">
                  <span className="text-[9px] font-bold tracking-wider text-slate-500 uppercase">Đang ký quỹ (Held in Escrow)</span>
                  <h3 className="text-base font-black text-indigo-400">1,500,000 VND</h3>
                  <p className="text-[9px] text-slate-600">Đơn hàng dài hạn đang được hệ thống BKS tạm giữ</p>
                </div>
                <div className="bg-slate-900 border border-slate-800 p-4 rounded-2xl space-y-1">
                  <span className="text-[9px] font-bold tracking-wider text-slate-500 uppercase">Cọc đã hoàn (Refunded)</span>
                  <h3 className="text-base font-black text-emerald-400">14,200,000 VND</h3>
                  <p className="text-[9px] text-slate-600">Tiền cọc đã trả lại cho khách hàng check-out</p>
                </div>
                <div className="bg-slate-900 border border-slate-800 p-4 rounded-2xl space-y-1">
                  <span className="text-[9px] font-bold tracking-wider text-slate-500 uppercase">Khấu trừ tài sản (Deducted)</span>
                  <h3 className="text-base font-black text-rose-400">450,000 VND</h3>
                  <p className="text-[9px] text-slate-600">Tiền phạt đền bù hỏng hóc thiết bị phòng</p>
                </div>
              </div>

              {/* simulated Audit list for uploads */}
              <div className="bg-slate-900 border border-slate-800 rounded-3xl p-4 space-y-3">
                <span className="text-xs font-bold text-white block border-b border-slate-800 pb-2">Danh sách chờ duyệt cọc thủ công</span>
                
                {deposits.filter(d => d.status === "payment_submitted").length === 0 ? (
                  <p className="text-xs text-slate-500 text-center py-4">Không có biên lai nào chờ duyệt.</p>
                ) : (
                  deposits.filter(d => d.status === "payment_submitted").map(item => (
                    <div key={item.id} className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 p-3 bg-slate-950 rounded-2xl border border-slate-800">
                      <div className="space-y-1 text-xs">
                        <p className="font-bold text-slate-200">{item.guest}</p>
                        <p className="text-[10px] text-slate-400">{item.room}</p>
                        <p className="font-mono text-indigo-400 font-bold">{item.amount.toLocaleString()} VND</p>
                      </div>

                      <div className="flex items-center gap-3 w-full sm:w-auto justify-between sm:justify-end">
                        {/* simulated Receipt preview */}
                        <div className="flex items-center gap-1.5 border border-slate-800 p-1 rounded-lg bg-slate-900 cursor-pointer hover:border-slate-600 transition-all">
                          <img src={item.receipt} alt="Receipt preview" className="w-8 h-8 object-cover rounded" />
                          <span className="text-[9px] text-sky-400 font-semibold pr-1">Xem biên lai</span>
                        </div>

                        <div className="flex gap-2">
                          <button
                            onClick={() => handleApproveDeposit(item.id)}
                            className="bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[10px] px-3 py-1.5 rounded-lg transition-all active:scale-95"
                          >
                            Duyệt nhận tiền
                          </button>
                          <button
                            className="border border-red-500/20 text-red-400 hover:bg-red-950/20 font-bold text-[10px] px-3 py-1.5 rounded-lg transition-all active:scale-95"
                          >
                            Hủy bỏ
                          </button>
                        </div>
                      </div>
                    </div>
                  ))
                )}
              </div>
            </div>
          )}

          {/* Footer controls info */}
          <div className="border-t border-slate-800 pt-4 mt-6 flex justify-between items-center text-[10px] text-slate-500">
            <span>Thiết kế bởi: BKS Platform BA & Architects</span>
            <span>Phiên bản v1.0 • React wireframe preview</span>
          </div>

        </div>
      </div>
    </div>
  );
}
