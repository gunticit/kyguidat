use App\Traits\HasFileUpload;

class ConsignmentService
{
    use HasFileUpload;
    
    public function handleUpload($file)
    {
        // Tự động upload lên S3 nếu có config, 
        // không thì upload local
        return $this->uploadFile($file, 'consignments');
    }
}

AWS_ACCESS_KEY_ID=your-key
AWS_SECRET_ACCESS_KEY=your-secret
AWS_BUCKET=your-bucket
AWS_DEFAULT_REGION=ap-southeast-1