<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Folder;
use App\Models\Document;
use App\Models\DocumentHistory;
use App\Models\AuditLog;
class CqcVaultController extends Controller
{

    public function auditLogs()
    {
        $logs = AuditLog::latest()->paginate(30);
        return view('admin.cqc.audit', compact('logs'));
    }
    public function dashboard()
    {
        return view('admin.cqc.dashboard');
    }
    public function checklistfrequency()
    {
        return view('admin.cqc.checklist_frequency');
    }
    public function checklist()
    {
        return view('admin.cqc.checklist_cqc');
    }

    public  function auditLog($action, $summary, $subject = null)
    {
        AuditLog::create([
            'user_id' => "1",
            'action' => $action,
            'summary' => $summary,
            'subject_type' => $subject ? class_basename($subject) : null,
            'subject_id' => $subject?->id,
            'ip_address' => request()->ip(),
        ]);
    }
    public function index()
    {
        $folders = Folder::whereNull('parent_id')->where('status','0')->get();
        
        return view('admin.cqc.index', compact('folders'));
    }

    public function viewFolder($id)
    {
        $folder = Folder::with(['children','documents'])->where('status','0')->findOrFail($id);
        // dd($folder);    
        return view('admin.cqc.folder',compact('folder'));
    }



    public function upload(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'file'  => 'required|file|max:10240'
        ]);

        $file = $request->file('file');

        // Create a unique filename
        $filename = time().'_'.$file->getClientOriginalName();
        $destinationPath = public_path('cqc');

        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }
        $file->move($destinationPath, $filename);

        // Save in DB (file_path relative to public/)
        $doc = Document::create([
            'folder_id'   => $request->folder_id,
            'title'       => $request->title,
            'file_path'   => 'cqc/'.$filename,
            'uploaded_by' => 1
        ]);

        $folder = Folder::find($request->folder_id);
        DocumentHistory::create([
            'document_id' => $doc->id,
            'action'      => 'uploaded',
            'file_path'   => 'cqc/'.$filename,
            'created_at'  => now()
        ]);
        $this->auditLog(
            'document_uploaded',
            'Document "' . $request->title . '" uploaded to folder "' . $folder->name . '"',
            $doc
        );

        return back()->with('success','Document Uploaded');
    }




    public function history($id)
    {
        $history = DocumentHistory::where('document_id',$id)->get();
        return response()->json($history);
    }

    public function createFolder(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $folder = Folder::create([
            'name'      => $request->name,
            'parent_id' => $request->parent_id ?: null,
            'year'      => $request->year ?: null
        ]);


        $this->auditLog(
            'folder_created',
            'Folder "' . $folder->name . '" created',
            $folder
        );
        return back()->with('success','Folder created successfully');
    }

    // Add multiple subfolders under current folder
    // public function addSubfolders(Request $request, $folderId)
    // {
    //     $parentFolder = Folder::findOrFail($folderId);

    //     // ❌ Agar ye already subfolder hai to allow mat karo
    //     if ($parentFolder->parent_id !== null) {
    //         return back()->with('error', 'Subfolder ke andar aur subfolder create nahi ho sakta');
    //     }
    //     $request->validate([
    //         'names' => 'required|array',
    //         'names.*' => 'required|string|max:255',
    //     ]);
    //     foreach ($request->names as $name) {
    //         Folder::create([
    //             'name' => $name,
    //             'parent_id' => $folderId,
    //         ]);
    //     }
    //     return back()->with('success', 'Subfolder(s) created successfully');
    // }

    public function addSubfolders(Request $request, $folderId)
    {
        $request->validate([
            'names' => 'required|array',
            'names.*' => 'required|string|max:255',
        ]);

        foreach ($request->names as $name) {
            $sub = Folder::create([
                'name' => $name,
                'parent_id' => $folderId,
            ]);
             $this->auditLog(
                'subfolder_created',
                'Subfolder "' . $name . '" created under folder Name :-' . $name,
                $sub
            );
        }

        return back()->with('success', 'Subfolder(s) created successfully');
    }


    // Delete a folder (and optionally its subfolders and documents)
    public function dFolder($id)
    {
        $folder = Folder::findOrFail($id);

        // Optional: delete subfolders recursively
        foreach($folder->children as $child) {
            $child->status = '1';
            $child->save();
            $this->auditLog(
                'subfolder_deleted',
                'Subfolder "' . $child->name . '" deleted',
                $child
            );
        }

        // Optional: delete documents inside
        foreach($folder->documents as $doc) {
            if(file_exists(public_path($doc->file_path))) {
                unlink(public_path($doc->file_path));
            }
            // $doc->delete();
        }

        $folder->status = '1';
        $folder->save();

        return back()->with('success', 'Folder deleted successfully');
    }

    // Delete a subfolder
    public function deleteFolder($id)
    {
        $folder = Folder::findOrFail($id);
        $folder->status = '1';
        $folder->save();

        $this->auditLog(
            'folder_deleted',
            'Folder "' . $folder->name . '" deleted',
            $folder
        );

        return back()->with('success', 'Folder deleted successfully');
    }

    public function deleteDocument($id)
    {
        $doc = Document::findOrFail($id);

        if ($doc->file_path && file_exists(public_path($doc->file_path))) {
            unlink(public_path($doc->file_path));
        }

        DocumentHistory::where('document_id', $doc->id)->delete();

        // Delete document record
        $doc->delete();
        $this->auditLog(
            'document_deleted',
            'Document "' . $doc->title . '" deleted',
            $doc
        );

        return back()->with('success', 'Document deleted successfully');
    }


}



