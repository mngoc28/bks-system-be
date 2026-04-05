declare(strict_types=1);

namespace App\Repositories\ChatbotAnswerRepository;

use App\Repositories\RepositoryInterface;

/**
 * Interface ChatbotAnswerRepositoryInterface
 *
 * @package App\Repositories\ChatbotAnswerRepository
 */
interface ChatbotAnswerRepositoryInterface extends RepositoryInterface
{
    /**
     * Get all chatbot answers grouped by their respective questions
     *
     * @return array
     */
    public function getAllGroupedByQuestion(): array;
}
