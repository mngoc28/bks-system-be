// @ts-nocheck
import React, { useState } from "react";

// Inline SVG Icons matching the BKS System UI style
const SuccessCheckIcon = () => (
  <svg className="w-12 h-12 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2.5}>
    <path strokeLinecap="round" strokeLinejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
  </svg>
);

const CheckIcon = () => (
  <svg className="w-5 h-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={3}>
    <path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7" />
  </svg>
);

const QrIcon = () => (
  <svg className="w-28 h-28 text-slate-800" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1}>
    <path strokeLinecap="round" strokeLinejoin="round" d="M12 4v1m0 11v1m0-6V8m0-4H8m8 0h-4m4 0h4m-4 12h4m-4-6h4M4 8h4m-4 4h4m-4 4h4M4 4h16v16H4V4z" />
  </svg>
);

const AlertIcon = () => (
  <svg className="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
    <path strokeLinecap="round" strokeLinejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
  </svg>
);

const CopyIcon = () => (
  <svg className="w-3.5 h-3.5 text-slate-400 hover:text-indigo-600 cursor-pointer" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
    <path strokeLinecap="round" strokeLinejoin="round" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
  </svg>
);

const GlobeIcon = () => (
  <svg className="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
    <path strokeLinecap="round" strokeLinejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9-9c1.657 0 3 4.03 3 9s-1.343 9-3 9m0-18c-1.657 0-3 4.03-3 9s1.343 9 3 9m-9-9a9 9 0 019-9" />
  </svg>
);

export default function BookingDepositUIV2() {
  const [copied, setCopied] = useState<string | null>(null);

  const triggerCopy = (text: string, label: string) => {
    setCopied(label);
    navigator.clipboard?.writeText?.(text);
    setTimeout(() => setCopied(null), 2000);
  };

  return (
    <div className="w-full bg-slate-100 text-slate-900 flex flex-col font-sans select-none border border-slate-250 shadow-lg rounded-2xl overflow-hidden">
      
      {/* Top Header of BKS System */}
      <header className="bg-white border-b border-slate-200 px-8 py-3.5 flex justify-between items-center">
        <div className="flex items-center gap-2">
          {/* Logo BKS System */}
          <div className="bg-[#002f6c] text-white px-3 py-1.5 rounded-lg text-xs font-black tracking-wider flex items-center gap-1 shadow-sm">
            <span className="bg-sky-400 text-[#002f6c] px-1 rounded-sm text-[10px] font-black">BKS</span>
            <span>BKS System</span>
          </div>
        </div>
        <div className="flex items-center gap-5 text-[11px] font-bold text-slate-600">
          <span className="hover:text-blue-600 cursor-pointer">📞 Liên hệ</span>
          <span className="hover:text-blue-600 cursor-pointer">⭐ Điểm thưởng</span>
          <span className="hover:text-blue-600 cursor-pointer">📋 Đặt phòng của tôi</span>
          <button className="flex items-center gap-1 border border-slate-200 rounded-full px-3 py-1 bg-white hover:bg-slate-50 text-[10px]">
            <GlobeIcon />
            <span>Tiếng Việt</span>
            <span className="text-[8px] text-slate-400">▼</span>
          </button>
        </div>
      </header>

      {/* Hero Blue Banner Section */}
      <section className="bg-[#091b35] text-white text-center py-10 relative overflow-hidden">
        {/* Subtle grid lines background */}
        <div 
          className="absolute inset-0 opacity-[0.05]"
          style={{
            backgroundImage: 'radial-gradient(circle, #ffffff 1px, transparent 1px)',
            backgroundSize: '16px 16px',
          }}
        />
        <div className="relative z-10 flex flex-col items-center space-y-3 px-4">
          <SuccessCheckIcon />
          <h1 className="text-2xl font-black tracking-tight text-white">Đặt phòng thành công</h1>
          
          <div className="bg-[#ffffff0d] border border-white/10 rounded-2xl p-4 max-w-2xl text-xs text-slate-200 leading-relaxed shadow-sm">
            <p>Yêu cầu của bạn đã được ghi nhận trên hệ thống. Chúng tôi đã gửi thông tin xác nhận chi tiết về email mà bạn đã đăng ký.</p>
            <div className="h-px w-3/4 bg-white/15 my-2 mx-auto" />
            <p className="text-[11px] text-slate-400 flex items-center justify-center gap-1.5">
              <span className="bg-amber-50 text-amber-800 border border-amber-250 px-2 py-0.5 rounded-full font-bold text-[9px] uppercase tracking-wider scale-95">
                Quan trọng
              </span>
              Vui lòng kiểm tra kỹ hộp thư đến và cả thư mục <span className="font-semibold text-sky-400 italic">Spam (Thư rác)</span> nếu không thấy email.
            </p>
          </div>
        </div>
      </section>

      {/* Breadcrumbs bar */}
      <div className="bg-slate-50 border-b border-slate-200 px-8 py-2.5 text-[10px] font-semibold text-slate-500">
        <span>Trang chủ</span> <span className="text-slate-350 mx-1.5">/</span> <span className="text-slate-900">Đặt phòng thành công</span>
      </div>

      {/* Main Content Area */}
      <main className="mx-auto w-full max-w-xl px-4 py-8">
        
        {/* Main white Card */}
        <div className="bg-white border border-slate-200 rounded-[24px] p-6 shadow-md shadow-slate-200/40 space-y-6">
          
          {/* Booking Code Header */}
          <div className="flex flex-col items-center text-center space-y-1 pb-4 border-b border-slate-100">
            <span className="text-[9px] font-black uppercase tracking-wider text-slate-400">Mã đặt phòng của bạn</span>
            <span className="text-lg font-mono font-black text-slate-800 bg-slate-50 border border-slate-200 px-4 py-1.5 rounded-xl">
              RM-2826-008576
            </span>
            <p className="text-[10px] text-slate-500">
              Thông tin chi tiết đã được gửi tới: <span className="text-blue-600 font-bold">hominhngoc123456@gmail.com</span>
            </p>
          </div>

          {/* INTEGRATED DEPOSIT BLOCK (v2 - Option 1) */}
          <div className="bg-slate-50 border border-slate-200 rounded-2xl p-4 space-y-4 shadow-inner">
            {/* Warning banner */}
            <div className="bg-rose-50 border border-rose-200 px-3 py-2 rounded-xl flex items-center gap-2 text-rose-800 font-extrabold text-[10px] uppercase tracking-wider">
              <span className="bg-rose-500 text-white px-1.5 py-0.5 rounded font-black text-[9px]">CẢNH BÁO</span>
              <span>Đơn đặt phòng đang chờ thanh toán đặt cọc</span>
            </div>

            {/* Countdown bar */}
            <div className="bg-slate-100 border border-slate-200 p-3 rounded-xl text-center space-y-1">
              <span className="text-[9px] font-black text-slate-400 block">CÒN LẠI ĐỂ GIỮ PHÒNG:</span>
              <span className="font-mono text-2xl font-black text-rose-600 tracking-wider">01:59:42</span>
            </div>

            {/* Text description */}
            <p className="text-[10px] text-slate-500 leading-relaxed text-center">
              Để đảm bảo đặt phòng không bị hủy tự động, vui lòng truy cập **BKS Stay Portal** của bạn để kiểm tra thông tin và thực hiện đặt cọc giữ chỗ.
            </p>
          </div>

          {/* Guidelines Steps Section */}
          <div className="space-y-4">
            <h3 className="text-xs font-black uppercase tracking-wider text-slate-800 flex items-center gap-1.5">
              <CheckIcon /> HƯỚNG DẪN CÁC BƯỚC TIẾP THEO
            </h3>

            <div className="space-y-3 text-[11px] text-slate-600">
              {/* Step 1 */}
              <div className="flex gap-3 p-3 rounded-xl bg-slate-50 border border-slate-150">
                <div className="flex size-7 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600 font-bold text-xs">
                  01
                </div>
                <div className="space-y-0.5">
                  <p className="font-bold text-slate-800">Kiểm tra Email</p>
                  <p className="text-[10px] text-slate-500 leading-relaxed">
                    Một email xác nhận đã được gửi đến hòm thư của bạn. Vui lòng kiểm tra kỹ.
                  </p>
                </div>
              </div>

              {/* Step 2 */}
              <div className="flex gap-3 p-3 rounded-xl bg-slate-50 border border-slate-150">
                <div className="flex size-7 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600 font-bold text-xs">
                  02
                </div>
                <div className="space-y-0.5">
                  <p className="font-bold text-slate-800">Kích hoạt tài khoản & Đăng nhập</p>
                  <p className="text-[10px] text-slate-500 leading-relaxed">
                    Sử dụng liên kết trong email để kích hoạt tài khoản và đăng nhập BKS Stay Portal.
                  </p>
                </div>
              </div>

              {/* Step 3 */}
              <div className="flex gap-3 p-3 rounded-xl bg-slate-50 border border-slate-150">
                <div className="flex size-7 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600 font-bold text-xs">
                  03
                </div>
                <div className="space-y-0.5">
                  <p className="font-bold text-slate-800">Thanh toán cọc & Tải Voucher</p>
                  <p className="text-[10px] text-slate-500 leading-relaxed">
                    Truy cập chi tiết đặt phòng trên Portal để thanh toán cọc và tải Phiếu xác nhận lưu trú (Stay Voucher) hoặc ký hợp đồng thuê.
                  </p>
                </div>
              </div>
            </div>
          </div>

          {/* CTA Action Buttons */}
          <div className="flex flex-col sm:flex-row gap-3 pt-3 border-t border-slate-100">
            <button className="flex-1 h-10 rounded-xl font-bold text-xs bg-[#005bb8] hover:bg-[#004b99] text-white shadow-sm transition-all active:scale-98">
              Tới BKS Stay Portal
            </button>
            <button className="flex-1 h-10 rounded-xl font-bold text-xs bg-white border border-slate-250 text-slate-700 hover:bg-slate-50 transition-all active:scale-98">
              Tiếp tục tìm phòng
            </button>
          </div>

        </div>
      </main>

      {/* Dark Footer of BKS System */}
      <footer className="bg-[#0b172a] text-slate-400 text-xs px-8 py-8 border-t border-slate-800">
        <div className="max-w-4xl mx-auto grid grid-cols-1 md:grid-cols-4 gap-6">
          <div className="space-y-2">
            <div className="bg-[#ffffff0d] text-white px-2 py-1 rounded inline-block text-[10px] font-black tracking-wider">
              BKS System
            </div>
            <p className="text-[10px] leading-relaxed text-slate-500">
              BKS System tuyển chọn chỗ ở chất lượng với thông tin minh bạch, giúp bạn tìm và đặt phòng nhanh chóng cho mọi chuyến đi.
            </p>
          </div>
          <div className="space-y-1.5 text-[10px]">
            <p className="font-black text-slate-200 uppercase tracking-wider text-[9px]">Khám Phá</p>
            <p className="hover:text-white cursor-pointer">Phòng & Căn hộ</p>
            <p className="hover:text-white cursor-pointer">Điểm lưu trú</p>
            <p className="hover:text-white cursor-pointer">Cẩm nang du lịch</p>
          </div>
          <div className="space-y-1.5 text-[10px]">
            <p className="font-black text-slate-200 uppercase tracking-wider text-[9px]">Đối tác liên kết</p>
            <p className="hover:text-white cursor-pointer text-sky-400 font-bold">Đăng ký đối tác mới</p>
            <p className="hover:text-white cursor-pointer">Cổng thông tin đối tác</p>
            <p className="hover:text-white cursor-pointer">Chính sách đối tác</p>
          </div>
          <div className="space-y-1.5 text-[10px]">
            <p className="font-black text-slate-200 uppercase tracking-wider text-[9px]">Hỗ trợ</p>
            <p className="hover:text-white cursor-pointer">Liên hệ</p>
            <p className="hover:text-white cursor-pointer">Câu hỏi thường gặp</p>
            <p className="hover:text-white cursor-pointer">Chính sách bảo mật</p>
          </div>
        </div>
        <div className="h-px bg-slate-800 my-6 max-w-4xl mx-auto" />
        <div className="max-w-4xl mx-auto flex justify-between items-center text-[9px] text-slate-650">
          <span>© 2026 BKS System. Trải nghiệm đặt phòng lưu trú đáng tin cậy.</span>
          <span>Sử dụng • Cookie • Quan hệ đầu tư</span>
        </div>
      </footer>

    </div>
  );
}
