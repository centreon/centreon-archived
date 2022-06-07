import parse from 'html-react-parser';
import DOMPurify from 'dompurify';

import { Typography, Theme } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';
import { CreateCSSProperties } from '@mui/styles';

import truncate from '../../truncate';

type StylesProps = Pick<Props, 'bold'>;

const useStyles = makeStyles<Theme, StylesProps>(() => ({
  information: ({ bold }): CreateCSSProperties<StylesProps> => ({
    fontWeight: bold ? 600 : 'unset',
  }),
}));

interface Props {
  bold?: boolean;
  content?: string;
}

const OutputInformation = ({ content, bold = false }: Props): JSX.Element => {
  const classes = useStyles({ bold });

  return (
    <Typography className={classes.information} variant="body2">
      {parse(DOMPurify.sanitize(truncate(content)))}
    </Typography>
  );
};

export default OutputInformation;
