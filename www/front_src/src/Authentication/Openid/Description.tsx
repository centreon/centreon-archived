import { useTranslation } from 'react-i18next';

import { Typography } from '@mui/material';

import {
  labelAndAnAccessGroup,
  labelAuthorizationKeyDefinedByDefault,
  labelAuthorizationKeyWithPunctuation,
  labelAuthorizationSummary,
  labelContactGroupFieldDescription,
  labelDefineRelationsDescription,
  labelGroups,
  labelIfWeConsiderTheFollowingExample,
  labelTechnical,
} from './translatedLabels';

const Description = (): JSX.Element => {
  const { t } = useTranslation();

  return (
    <>
      <Typography>{t(labelAuthorizationSummary)}</Typography>
      <br />
      <Typography>- {t(labelContactGroupFieldDescription)}</Typography>
      <Typography>
        - {t(labelAuthorizationKeyWithPunctuation)}{' '}
        <code>{t(labelGroups)}</code> {t(labelAuthorizationKeyDefinedByDefault)}
      </Typography>
      <Typography>- {t(labelDefineRelationsDescription)}</Typography>
      <br />
      <Typography>
        {t(labelIfWeConsiderTheFollowingExample)}{' '}
        <code>{t(labelTechnical)}</code> {t(labelAndAnAccessGroup)}
      </Typography>
      <pre>
        {JSON.stringify(
          {
            email: 'test@example.com',
            groups: 'technical,HW',
            sub: '2',
            username: 'test',
          },
          null,
          2,
        )}
      </pre>
    </>
  );
};

export default Description;
