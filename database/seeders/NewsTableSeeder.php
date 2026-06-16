<?php

declare(strict_types=1);

namespace Database\Seeders;

use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class NewsTableSeeder extends Seeder
{
    /**
     * Remove Vietnamese accents from string
     */
    private function removeVietnameseAccents(string $str): string
    {
        $accents = [
            'à',
            'á',
            'ạ',
            'ả',
            'ã',
            'â',
            'ầ',
            'ấ',
            'ậ',
            'ẩ',
            'ẫ',
            'ă',
            'ằ',
            'ắ',
            'ặ',
            'ẳ',
            'ẵ',
            'è',
            'é',
            'ẹ',
            'ẻ',
            'ẽ',
            'ê',
            'ề',
            'ế',
            'ệ',
            'ể',
            'ễ',
            'ì',
            'í',
            'ị',
            'ỉ',
            'ĩ',
            'ò',
            'ó',
            'ọ',
            'ỏ',
            'õ',
            'ô',
            'ồ',
            'ố',
            'ộ',
            'ổ',
            'ỗ',
            'ơ',
            'ờ',
            'ớ',
            'ợ',
            'ở',
            'ỡ',
            'ù',
            'ú',
            'ụ',
            'ủ',
            'ũ',
            'ư',
            'ừ',
            'ứ',
            'ự',
            'ử',
            'ữ',
            'ỳ',
            'ý',
            'ỵ',
            'ỷ',
            'ỹ',
            'đ',
            'À',
            'Á',
            'Ạ',
            'Ả',
            'Ã',
            'Â',
            'Ầ',
            'Ấ',
            'Ậ',
            'Ẩ',
            'Ẫ',
            'Ă',
            'Ằ',
            'Ắ',
            'Ặ',
            'Ẳ',
            'Ẵ',
            'È',
            'É',
            'Ẹ',
            'Ẻ',
            'Ẽ',
            'Ê',
            'Ề',
            'Ế',
            'Ệ',
            'Ể',
            'Ễ',
            'Ì',
            'Í',
            'Ị',
            'Ỉ',
            'Ĩ',
            'Ò',
            'Ó',
            'Ọ',
            'Ỏ',
            'Õ',
            'Ô',
            'Ồ',
            'Ố',
            'Ộ',
            'Ổ',
            'Ỗ',
            'Ơ',
            'Ờ',
            'Ớ',
            'Ợ',
            'Ở',
            'Ỡ',
            'Ù',
            'Ú',
            'Ụ',
            'Ủ',
            'Ũ',
            'Ư',
            'Ừ',
            'Ứ',
            'Ự',
            'Ử',
            'Ữ',
            'Ỳ',
            'Ý',
            'Ỵ',
            'Ỷ',
            'Ỹ',
            'Đ',
        ];

        $noAccents = [
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'e',
            'e',
            'e',
            'e',
            'e',
            'e',
            'e',
            'e',
            'e',
            'e',
            'e',
            'i',
            'i',
            'i',
            'i',
            'i',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'u',
            'u',
            'u',
            'u',
            'u',
            'u',
            'u',
            'u',
            'u',
            'u',
            'u',
            'y',
            'y',
            'y',
            'y',
            'y',
            'd',
            'A',
            'A',
            'A',
            'A',
            'A',
            'A',
            'A',
            'A',
            'A',
            'A',
            'A',
            'A',
            'A',
            'A',
            'A',
            'A',
            'A',
            'E',
            'E',
            'E',
            'E',
            'E',
            'E',
            'E',
            'E',
            'E',
            'E',
            'E',
            'I',
            'I',
            'I',
            'I',
            'I',
            'O',
            'O',
            'O',
            'O',
            'O',
            'O',
            'O',
            'O',
            'O',
            'O',
            'O',
            'O',
            'O',
            'O',
            'O',
            'O',
            'O',
            'U',
            'U',
            'U',
            'U',
            'U',
            'U',
            'U',
            'U',
            'U',
            'U',
            'U',
            'Y',
            'Y',
            'Y',
            'Y',
            'Y',
            'D',
        ];

        return str_replace($accents, $noAccents, $str);
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        DB::table('news')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        $faker = Faker::create('vi_VN');

        $userIds = DB::table('users')->pluck('id')->toArray();
        $adminPartnerIds = DB::table('users')
            ->whereIn('role', ['admin', 'partner'])
            ->pluck('id')
            ->toArray();

        if (empty($userIds)) {
            $this->command->warn('No users found. Please run UsersTableSeeder first.');
            return;
        }

        if (empty($adminPartnerIds)) {
            $adminPartnerIds = [1];
        }

        $articles = [
            [
                'title' => 'Ăn sập Đà Lạt: Top 10 quán ăn địa phương không thể bỏ qua',
                'summary' => 'Khám phá danh sách các quán ăn truyền thống và độc đáo tại Đà Lạt được người bản địa đánh giá cao nhất.',
                'content' => 'Không chỉ nổi tiếng với khí hậu mát mẻ và ngàn hoa, Đà Lạt còn sở hữu nền ẩm thực vô cùng đa dạng. Cùng BKSStay điểm qua các món ăn sáng ấm nóng như bánh căn lòng gà, lẩu gà lá é, hay nem nướng nổi tiếng. Bài viết cung cấp chi tiết địa chỉ, mức giá và khung giờ mở cửa để bạn lên lịch trình hoàn hảo nhất.',
                // Vietnamese street food / Asian noodle bowl
                'image_url' => 'https://images.pexels.com/photos/1640777/pexels-photo-1640777.jpeg?auto=compress&cs=tinysrgb&w=800',
            ],
            [
                'title' => 'Lịch trình du lịch Vũng Tàu 2 ngày 1 đêm tự túc siêu tiết kiệm',
                'summary' => 'Gợi ý chi tiết lịch trình ăn chơi, check-in các điểm hot nhất tại Vũng Tàu từ thứ Bảy đến Chủ Nhật.',
                'content' => 'Vũng Tàu là lựa chọn hoàn hảo cho kỳ nghỉ cuối tuần ngắn ngày. Bài viết này hướng dẫn bạn tối ưu thời gian từ khâu di chuyển, ghé thăm Đồi Con Heo, ngắm hoàng hôn tại Mũi Nghinh Phong, thưởng thức bánh khọt Gốc Vú Sữa và gợi ý đặt phòng Homestay gần biển để di chuyển dễ dàng.',
                // Tropical beach clear turquoise water
                'image_url' => 'https://images.pexels.com/photos/1032650/pexels-photo-1032650.jpeg?auto=compress&cs=tinysrgb&w=800',
            ],
            [
                'title' => 'Hà Nội 36 phố phường: Khám phá những góc check-in cổ kính',
                'summary' => 'Gợi ý những góc chụp ảnh phong cách retro và cổ điển mang đậm hơi thở Tràng An giữa lòng thủ đô.',
                'content' => 'Dành cho những tâm hồn yêu Hà Nội, đây là hành trình đi qua những ngõ nhỏ cổ kính, Nhà thờ Lớn, phố bích họa Phùng Hưng và thưởng thức cà phê trứng trứ danh. Khám phá ngay các studio homestay mang phong cách Indochine độc đáo mà bạn có thể trải nghiệm khi đặt phòng tại BKSStay.',
                // Hanoi / Asian old town street with lanterns
                'image_url' => 'https://images.pexels.com/photos/3408744/pexels-photo-3408744.jpeg?auto=compress&cs=tinysrgb&w=800',
            ],
            [
                'title' => 'Đón hè rực rỡ: Combo Đặt phòng sớm giảm đến 25% kèm đưa đón',
                'summary' => 'Ưu đãi đặc biệt cho mùa du lịch cao điểm khi đặt phòng trước 30 ngày trên toàn hệ thống BKSStay.',
                'content' => 'Chào hè năng động, BKSStay mang đến chương trình "Early Bird" tri ân khách hàng. Khi lên kế hoạch sớm và đặt trước tối thiểu 30 ngày, bạn không chỉ nhận mức chiết khấu phòng tốt nhất mà còn được tặng kèm voucher xe limousine đưa đón miễn phí hoặc giảm giá 15% cho các dịch vụ ăn uống.',
                // Summer tropical resort — sun loungers by the pool
                'image_url' => 'https://images.pexels.com/photos/261102/pexels-photo-261102.jpeg?auto=compress&cs=tinysrgb&w=800',
            ],
            [
                'title' => 'Ưu đãi ở dài hạn: Trải nghiệm "Workation" trọn gói giá tốt',
                'summary' => 'Gói lưu trú từ 7 ngày trở lên dành cho người làm việc từ xa với nhiều đặc quyền hấp dẫn.',
                'content' => 'Xu hướng Workation (làm việc kết hợp nghỉ dưỡng) đang ngày càng phổ biến. BKSStay cung cấp các căn hộ dịch vụ và phòng studio trang bị đầy đủ bàn làm việc, kết nối internet tốc độ cao và dịch vụ dọn dẹp hàng tuần với mức giá ưu đãi giảm đến 40% so với thuê lẻ ngày.',
                // Laptop on table — remote work / workation
                'image_url' => 'https://images.pexels.com/photos/4974914/pexels-photo-4974914.jpeg?auto=compress&cs=tinysrgb&w=800',
            ],
            [
                'title' => 'Hành trình tái hiện nét kiến trúc Hội An trong căn Homestay hiện đại',
                'summary' => 'Khám phá câu chuyện thiết kế của ngôi nhà gỗ truyền thống được cách tân giữa lòng phố cổ Hội An.',
                'content' => 'Với tình yêu dành cho di sản, chủ nhà BKSStay tại Hội An đã dành hơn 1 năm để phục dựng lại ngôi nhà gỗ cũ kỹ thành không gian homestay ấm cúng nhưng vẫn đầy đủ tiện nghi hiện đại. Từng chi tiết đèn lồng, bức tường vàng cổ kính đến gạch bông lát nền đều mang một câu chuyện văn hóa thú vị đang chờ du khách khám phá.',
                // Hoi An yellow walls / paper lanterns lit up
                'image_url' => 'https://images.pexels.com/photos/3075993/pexels-photo-3075993.jpeg?auto=compress&cs=tinysrgb&w=800',
            ],
            [
                'title' => 'Điểm danh 5 Villa hồ bơi riêng biệt cho kỳ nghỉ gia đình ấm cúng',
                'summary' => 'Top những căn Villa có hồ bơi nước tràn, sân vườn BBQ rộng lớn cho gia đình tụ họp cuối tuần.',
                'content' => 'Nếu bạn đang tìm kiếm sự riêng tư tuyệt đối cho gia đình, top 5 Villa giới thiệu trong bài viết này chính là câu trả lời. Với thiết kế mở, hồ bơi riêng sang trọng và không gian bếp đầy đủ tiện nghi, bạn có thể tự tay chuẩn bị tiệc nướng BBQ ngoài trời cùng những người thân yêu mà không lo bị làm phiền.',
                // Private villa infinity pool surrounded by lush garden
                'image_url' => 'https://images.pexels.com/photos/261169/pexels-photo-261169.jpeg?auto=compress&cs=tinysrgb&w=800',
            ],
            [
                'title' => 'Kinh nghiệm du lịch tự túc Đà Nẵng - Hội An cho gia đình có trẻ nhỏ',
                'summary' => 'Các lưu ý quan trọng về chọn phương tiện di chuyển, chuẩn bị hành lý và chọn phòng tiện nghi cho bé.',
                'content' => 'Du lịch cùng trẻ nhỏ luôn cần sự chuẩn bị kỹ lưu hơn. BKSStay chia sẻ kinh nghiệm thực tế giúp gia đình bạn có chuyến đi Đà Nẵng trọn vẹn: từ việc chọn căn hộ có bếp riêng để nấu cháo cho bé, chuẩn bị xe đẩy gọn nhẹ đến các điểm tham quan thân thiện với trẻ em.',
                // Da Nang — coastal city beach scene, family travel
                'image_url' => 'https://images.pexels.com/photos/1007657/pexels-photo-1007657.jpeg?auto=compress&cs=tinysrgb&w=800',
            ],
            [
                'title' => 'Mẹo chuẩn bị hành lý gọn nhẹ cho chuyến đi phượt bằng xe máy',
                'summary' => 'Cách xếp đồ thông minh giúp chuyến đi phượt an toàn, nhẹ nhàng mà vẫn đầy đủ dụng cụ cần thiết.',
                'content' => 'Một chuyến phượt bằng xe máy đòi hỏi sự tối giản tối đa trong hành lý. Hãy áp dụng phương pháp cuộn quần áo, mang theo đồ dùng đa năng và các vật dụng y tế cơ bản. Ngoài ra, việc chọn các điểm lưu trú dạng Homestay/Glamping của BKSStay có sẵn dịch vụ giặt là sẽ giúp bạn giảm đáng kể số lượng đồ mang theo.',
                // Motorcycle on winding mountain road / road trip
                'image_url' => 'https://images.pexels.com/photos/1157386/pexels-photo-1157386.jpeg?auto=compress&cs=tinysrgb&w=800',
            ],
            [
                'title' => 'Top 5 quán cà phê ngắm hoàng hôn siêu lãng mạn tại Phú Quốc',
                'summary' => 'Danh sách những quán cà phê view biển đẹp nhất để ngắm trọn vẹn hoàng hôn rực rỡ của đảo ngọc.',
                'content' => 'Ngắm hoàng hôn là trải nghiệm không thể bỏ lỡ khi đến Phú Quốc. Bài viết này giới thiệu 5 quán cà phê sát biển có thiết kế mở cực đẹp, đồ uống ngon và là điểm ngắm mặt trời lặn lý tưởng nhất. Cùng lưu lại danh sách và đặt phòng nghỉ BKSStay gần thị trấn Dương Đông.',
                // Golden ocean sunset — warm amber tones
                'image_url' => 'https://images.pexels.com/photos/1003436/pexels-photo-1003436.jpeg?auto=compress&cs=tinysrgb&w=800',
            ]
        ];

        $count = 30;
        for ($i = 1; $i <= $count; $i++) {
            $article = $articles[($i - 1) % count($articles)];
            $title = $article['title'];

            // Append a suffix for repeated template articles to make them unique
            $round = (int)(($i - 1) / count($articles));
            if ($round > 0) {
                $title .= ' - Phần ' . ($round + 1);
            }

            $slug = Str::slug($this->removeVietnameseAccents($title)) . '-' . $i;
            $userId = $faker->randomElement($userIds);
            $authorId = $faker->randomElement($adminPartnerIds);
            
            // Ensure we have a deterministic mix of drafts (status 0) and published (status 1)
            $status = $i % 3 === 0 ? 0 : 1;
            $publishedAt = $status === 1 ? Carbon::now()->subDays(rand(1, 30)) : null;

            DB::table('news')->insert([
                'user_id' => $userId,
                'title' => $title,
                'slug' => $slug,
                'summary' => $article['summary'],
                'content' => $article['content'],
                'status' => $status,
                'published_at' => $publishedAt,
                'image_url' => $article['image_url'],
                'id_image_cloudinary' => null,
                'created_by' => $authorId,
                'updated_by' => $faker->randomElement($adminPartnerIds),
                'created_at' => Carbon::now()->subDays(rand(1, 40)),
                'updated_at' => Carbon::now()->subDays(rand(1, 40)),
            ]);
        }
    }
}
