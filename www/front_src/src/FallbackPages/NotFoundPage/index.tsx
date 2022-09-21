import { useTranslation } from 'react-i18next';

import { FallbackPage } from '@centreon/ui';

import { labelThisPageCouldNotBeFound } from './translatedLabels';

const NotFoundPage = (): JSX.Element => {
  const { t } = useTranslation();

  return <FallbackPage message={t(labelThisPageCouldNotBeFound)} title="404" />;
};

export default NotFoundPage;
