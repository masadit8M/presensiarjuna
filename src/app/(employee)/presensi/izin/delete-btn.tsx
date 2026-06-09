'use client';

import React, { useTransition } from 'react';
import { useRouter } from 'next/navigation';
import { deleteIzinAction } from '@/lib/actions/izin';
import { Trash2, Loader2 } from 'lucide-react';

export default function DeleteIzinBtn({ kode_izin }: { kode_izin: string }) {
  const [isPending, startTransition] = useTransition();
  const router = useRouter();

  const handleDelete = () => {
    if (!confirm('Apakah Anda yakin ingin membatalkan pengajuan ini?')) {
      return;
    }

    startTransition(async () => {
      const result = await deleteIzinAction(kode_izin);
      if (result?.error) {
        alert(result.error);
      } else {
        router.refresh();
      }
    });
  };

  return (
    <button
      onClick={handleDelete}
      disabled={isPending}
      className="p-2 bg-red-500/10 hover:bg-red-500/20 text-red-400 hover:text-red-300 rounded-xl transition-colors disabled:opacity-50"
      title="Batalkan Pengajuan"
    >
      {isPending ? (
        <Loader2 className="w-4 h-4 animate-spin" />
      ) : (
        <Trash2 className="w-4 h-4" />
      )}
    </button>
  );
}
