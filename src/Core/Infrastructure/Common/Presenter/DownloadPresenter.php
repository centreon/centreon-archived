<?php

namespace Core\Infrastructure\Common\Presenter;

use Symfony\Component\HttpFoundation\Response;

class DownloadPresenter extends AbstractPresenter implements PresenterFormatterInterface
{
    public function __construct(private PresenterFormatterInterface $presenter) {}

    public function present(mixed $data): void
    {
        $this->presenter->present($data);
        $originalHeaders = $this->presenter->getResponseHeaders();
        $originalHeaders['Content-Type'] = 'application/force-download';
        $originalHeaders['Content-Disposition'] = 'attachment; filename="test.csv"';
        $this->presenter->setResponseHeaders($originalHeaders);
    }

    public function show(): Response
    {
        return $this->presenter->show();
    }
}
